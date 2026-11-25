<?php

namespace App\Services;

use App\Models\ConsultancyApplication as Application;
use App\Models\Order;
use App\Models\OrderExpertAssignment;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderDelivery
{
    public const TASK_STATUSES = ['todo' => 'To do', 'doing' => 'In progress', 'done' => 'Done'];

    public function __construct(private ExpertWallet $wallet) {}

    public function assign(Order $order, User $staff, array $ids, array $percentages = []): void
    {
        abort_unless($staff->can('update', $order) && ConsultancyEngagement::canAssign($staff), 403);
        $data = validator(['experts' => $ids], [
            'experts' => ['present', 'array', 'max:30'],
            'experts.*' => ['required', 'integer', 'distinct'],
        ])->validate();

        DB::transaction(function () use ($order, $data, $percentages): void {
            $locked = $this->lock($order);
            $existing = $locked->expertAssignments()->get()->keyBy('consultancy_application_id');
            $this->requireUnpaid($locked);

            $ids = $data['experts'];
            $experts = Application::whereIn('id', $ids)->assignableToOrders()->orderBy('id')->lockForUpdate()->get();
            if ($experts->count() !== count($ids)) {
                $this->fail('experts', 'Choose approved experts who are eligible for order assignments.');
            }

            $locked->expertAssignments()->whereNotIn('consultancy_application_id', $ids)->delete();
            foreach ($experts as $expert) {
                $assignment = $existing->get($expert->id)
                    ?? $locked->expertAssignments()->make(['consultancy_application_id' => $expert->id]);
                $assignment->engagement_percentage = $expert->contract_type === null
                    ? validator(
                        ['percentages' => [$expert->id => $percentages[$expert->id] ?? $assignment->engagement_percentage]],
                        ["percentages.{$expert->id}" => ConsultancyEngagement::PERCENTAGE_RULES],
                    )->validate()['percentages'][$expert->id]
                    : ($assignment->engagement_percentage ?? $expert->engagement_percentage ?? '0.00');
                $assignment->save();
            }
        });
    }

    public function plan(Order $order, User $staff, array $milestones): void
    {
        abort_unless($staff->can('update', $order), 403);
        $data = validator(['milestones' => $milestones], [
            'milestones' => ['present', 'array', 'max:20'],
            'milestones.*.id' => ['nullable', 'uuid', 'distinct'],
            'milestones.*.title' => ['required', 'string', 'max:120'],
            'milestones.*.due' => ['nullable', 'date_format:Y-m-d'],
            'milestones.*.tasks' => ['present', 'array', 'max:20'],
            'milestones.*.tasks.*.id' => ['nullable', 'uuid', 'distinct'],
            'milestones.*.tasks.*.title' => ['required', 'string', 'max:140'],
            'milestones.*.tasks.*.description' => ['nullable', 'string', 'max:600'],
        ])->validate();

        DB::transaction(function () use ($order, $data): void {
            $locked = $this->lock($order);
            $this->requireUnpaid($locked);
            $locked->update(['delivery_milestones' => self::buildPlan($data['milestones'], $locked->delivery_milestones ?? [])]);
        });
    }

    public static function initialWorkOrderPlan(WorkOrder $workOrder): ?array
    {
        $milestones = $workOrder->deliveryMilestones();

        return $milestones === [] ? null : self::buildPlan($milestones, []);
    }

    private static function buildPlan(array $milestones, array $previousPlan): array
    {
        $saved = collect($previousPlan)->keyBy('id');

        return collect($milestones)->map(function (array $milestone) use ($saved): array {
            $previous = $saved->get($milestone['id'] ?? '');
            $previousTasks = collect($previous['tasks'] ?? [])->keyBy('id');
            $tasks = collect($milestone['tasks'])->map(function (array $task) use ($previousTasks): array {
                $previousTask = $previousTasks->get($task['id'] ?? '');

                return [
                    'id' => $previousTask['id'] ?? (string) Str::uuid(),
                    'title' => $task['title'],
                    'description' => $task['description'] ?? null,
                    'status' => $previousTask['status'] ?? 'todo',
                ];
            })->values()->all();

            $planned = [
                'id' => $previous['id'] ?? (string) Str::uuid(),
                'title' => $milestone['title'],
                'due' => $milestone['due'] ?? null,
                'completed_at' => $previous['completed_at'] ?? null,
                'tasks' => $tasks,
            ];

            $planned['tasks'] = self::tasksForMilestone($planned);

            return $planned;
        })->values()->all();
    }

    public static function tasksForMilestone(array $milestone): array
    {
        if (! empty($milestone['tasks']) || ! Str::isUuid($milestone['id'] ?? null)) {
            return $milestone['tasks'] ?? [];
        }

        return [[
            'id' => $milestone['id'],
            'title' => $milestone['title'],
            'description' => null,
            'status' => filled($milestone['completed_at'] ?? null) ? 'done' : 'todo',
        ]];
    }

    public function complete(Order $order, User $staff, string $milestoneId): void
    {
        abort_unless($staff->can('update', $order), 403);

        DB::transaction(function () use ($order, $milestoneId): void {
            $locked = $this->lock($order);
            $milestones = $locked->delivery_milestones ?? [];
            $index = $this->findIndex($milestones, $milestoneId);
            $milestones[$index]['completed_at'] ??= now()->toIso8601String();
            $milestones[$index]['tasks'] = self::tasksForMilestone($milestones[$index]);

            foreach ($milestones[$index]['tasks'] as &$task) {
                $task['status'] = 'done';
            }
            unset($task);

            $locked->update(['delivery_milestones' => $milestones]);
        });
    }

    public function moveTask(Order $order, Application $expert, string $milestoneId, string $taskId, string $status): array
    {
        abort_unless(isset(self::TASK_STATUSES[$status]), 422);

        return DB::transaction(function () use ($order, $expert, $milestoneId, $taskId, $status): array {
            $locked = Order::whereKey($order->id)
                ->whereHas('expertAssignments', fn ($query) => $query->where('consultancy_application_id', $expert->id))
                ->lockForUpdate()->firstOrFail();
            if ($locked->status === 'cancelled') {
                $this->fail('status', 'This order has been cancelled.');
            }

            $milestones = $locked->delivery_milestones ?? [];
            $index = $this->findIndex($milestones, $milestoneId);
            if (filled($milestones[$index]['completed_at'])) {
                $this->fail('status', 'This milestone is already complete.');
            }

            $milestones[$index]['tasks'] = self::tasksForMilestone($milestones[$index]);
            $taskIndex = $this->findIndex($milestones[$index]['tasks'], $taskId);
            $milestones[$index]['tasks'][$taskIndex]['status'] = $status;
            $locked->update(['delivery_milestones' => $milestones]);

            return self::progress($locked);
        });
    }

    public static function progress(Order $order): array
    {
        $milestones = collect($order->delivery_milestones);
        $fractions = $milestones->sum(function (array $milestone): float {
            if (filled($milestone['completed_at'])) {
                return 1;
            }

            $tasks = collect($milestone['tasks']);

            return $tasks->isEmpty() ? 0 : $tasks->where('status', 'done')->count() / $tasks->count();
        });

        return [
            'completed' => $milestones->whereNotNull('completed_at')->count(),
            'total' => $milestones->count(),
            'percent' => $milestones->isEmpty() ? 0 : (int) round(100 * $fractions / $milestones->count()),
        ];
    }

    public function earnings(Order $order): array
    {
        $assignments = $order->expertAssignments;
        $progress = self::progress($order);
        if ($assignments->isEmpty() || $progress['total'] === 0 || $order->status === 'cancelled') {
            return [];
        }

        $paid = min($order->paidMinor(), Money::toMinor($order->total));
        $base = intdiv($paid * $progress['completed'], $progress['total']);
        $divisor = 10000 * $assignments->count();
        $numerators = $assignments->mapWithKeys(fn (OrderExpertAssignment $row) => [
            $row->id => $base * Money::toMinor($row->engagement_percentage),
        ]);
        $totals = $numerators->map(fn (int $value) => intdiv($value, $divisor));
        if ($assignments->pluck('engagement_percentage')->unique()->count() === 1) {
            $pool = Money::percent($base, $assignments->first()->engagement_percentage);
            $remainder = $pool - $totals->sum();
            foreach ($totals->keys()->take($remainder) as $id) {
                $totals[$id]++;
            }
        }

        return $totals->all();
    }

    public function release(Order $order, User $staff): int
    {
        abort_unless($staff->isAdmin() && $staff->can('update', $order), 403);

        return DB::transaction(function () use ($order, $staff): int {
            $locked = $this->lock($order)->load('expertAssignments.application');
            $earnings = $this->earnings($locked);
            $released = 0;

            foreach ($locked->expertAssignments as $assignment) {
                $target = $earnings[$assignment->id] ?? 0;
                $amount = $target - $assignment->credited_minor;
                if ($amount <= 0) {
                    continue;
                }

                $application = $assignment->application;
                if ($application->trashed()) {
                    continue;
                }

                $this->wallet->credit($application, $staff, Money::fromMinor($amount), "{$locked->number} / earnings ".Money::fromMinor($target));
                $assignment->update(['credited_minor' => $target]);
                $released += $amount;
            }

            return $released;
        }, attempts: 3);
    }

    private function lock(Order $order): Order
    {
        $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
        if ($locked->status === 'cancelled') {
            $this->fail('order', 'This order has been cancelled.');
        }

        return $locked;
    }

    private function requireUnpaid(Order $order): void
    {
        if ($order->expertAssignments()->where('credited_minor', '>', 0)->exists()) {
            $this->fail('order', 'The team and milestone plan are fixed once wallet earnings have been released.');
        }
    }

    private function findIndex(array $rows, string $id): int
    {
        $index = array_search($id, array_column($rows, 'id'), true);
        abort_if($index === false, 404);

        return $index;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
