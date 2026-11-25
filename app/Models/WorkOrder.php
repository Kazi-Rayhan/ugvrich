<?php

namespace App\Models;

use App\Support\BanglaNumberWords;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * A service work order (সেবা কার্যাদেশ) on one of the controlled forms.
 *
 * The form's own wording comes from config/work_orders.php by sector; what
 * is written into it is kept in `details`. The money figures are always
 * worked out here from the fee lines, never taken from the form.
 */
class WorkOrder extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'accepted' => 'Accepted',
        'confirmed' => 'Confirmed',
        'signed' => 'Signed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'draft' => 'gray',
        'issued' => 'info',
        'accepted' => 'info',
        'confirmed' => 'success',
        'signed' => 'success',
        'completed' => 'primary',
        'cancelled' => 'danger',
    ];

    protected $guarded = ['id', 'number', 'total'];

    public const CONFIRMABLE = ['draft', 'issued', 'signed', 'accepted'];

    protected $casts = [
        'issue_date' => 'date',
        'details' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $order) {
            $order->number ??= 'TMP-'.Str::random(20);
        });

        // UGV-RICH/WO/ICT/2026-0007: the form's own numbering, with a sequence that can't collide.
        static::created(function (WorkOrder $order) {
            $order->forceFill(['number' => $order->numberFor($order->sector)])->saveQuietly();
        });

        static::saving(function (WorkOrder $order) {
            $order->computeFigures();

            if ($order->exists && $order->isDirty('sector')) {
                $order->number = $order->numberFor($order->sector);
            }
        });
    }

    public function numberFor(?string $sector): string
    {
        $code = self::sector($sector)['code'] ?? 'GEN';
        $year = ($this->created_at ?? now())->format('Y');

        return sprintf('UGV-RICH/WO/%s/%s-%04d', $code, $year, $this->id);
    }

    /** @return array<string, mixed> The sector's form text, or [] if unknown. */
    public static function sector(?string $key): array
    {
        return config('work_orders.sectors.'.$key, []);
    }

    /** @return array<string, string> key => "ICT — Smart ICT Services" */
    public static function sectorOptions(bool $includeDepartment = true): array
    {
        return collect(config('work_orders.sectors'))
            ->mapWithKeys(fn (array $s, string $key) => [
                $key => $includeDepartment
                    ? $s['code'].' — '.$s['name'].' ('.config("work_order_admin.sectors.{$key}.department", $s['department']).')'
                    : $s['name'],
            ])
            ->all();
    }

    /** The sector a service category belongs to, by its slug. */
    public static function sectorForCategory(?ServiceCategory $category): ?string
    {
        if (! $category) {
            return null;
        }

        foreach (config('work_orders.sectors') as $key => $sector) {
            if (in_array($category->slug, $sector['categories'] ?? [], true)) {
                return $key;
            }
        }

        return null;
    }

    public function detail(string $key, mixed $default = null): mixed
    {
        return data_get($this->details, $key, $default);
    }

    public function deliveryMilestones(): array
    {
        $milestones = collect($this->detail('milestones'))
            ->filter(fn (array $milestone) => filled($milestone['title'] ?? null))
            ->map(fn (array $milestone) => [
                'title' => $milestone['title'],
                'due' => $milestone['date'] ?? null,
                'tasks' => [],
            ])->values()->all();

        if ($milestones !== []) {
            return $milestones;
        }

        $selectedStage = $this->detail('interim_milestone');

        return collect(static::sector($this->sector)['stages'] ?? [])
            ->filter(fn (array $stage, int $index) => filled($this->detail("stage_dates.{$index}"))
                || (is_numeric($selectedStage) && (int) $selectedStage === $index))
            ->map(fn (array $stage, int $index) => [
                'title' => $stage[0],
                'due' => $this->detail("stage_dates.{$index}"),
                'tasks' => [['title' => $stage[1]]],
            ])->values()->all();
    }

    /**
     * Fee lines → subtotal, tax, grand total; instalment amounts from their
     * percentages; the total in Bangla words when none was typed.
     */
    public function computeFigures(): void
    {
        $details = $this->details ?? [];
        $subtotal = 0;

        foreach (range(0, 2) as $i) {
            $qty = (string) data_get($details, "fees.{$i}.qty", '');
            $rate = Money::toMinor(data_get($details, "fees.{$i}.rate"));
            $line = $rate === 0 ? 0 : Money::multiply($rate, is_numeric($qty) && $qty !== '' ? $qty : '1');
            data_set($details, "fees.{$i}.total", $rate === 0 ? null : Money::fromMinor($line));
            $subtotal += $line;
        }

        $tax = Money::toMinor(data_get($details, 'tax_amount'));
        $grand = data_get($details, 'tax_mode') === 'deduct' ? $subtotal - $tax : $subtotal + $tax;

        data_set($details, 'subtotal', Money::fromMinor($subtotal));
        data_set($details, 'grand_total', Money::fromMinor(max($grand, 0)));

        foreach (range(0, 2) as $i) {
            $percent = data_get($details, "installments.{$i}.percent");
            data_set($details, "installments.{$i}.amount", filled($percent) && is_numeric($percent)
                ? Money::fromMinor(Money::percent(max($grand, 0), $percent))
                : null);
        }

        if (blank(data_get($details, 'amount_words')) && $grand > 0) {
            data_set($details, 'amount_words_auto', BanglaNumberWords::taka(intdiv($grand, 100)));
        } else {
            data_set($details, 'amount_words_auto', null);
        }

        $this->details = $details;
        $this->total = Money::fromMinor(max($grand, 0));
        $this->client_name = data_get($details, 'client.name') ?: $this->client_name;
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function consultancyRequest(): BelongsTo
    {
        return $this->belongsTo(ConsultancyRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
