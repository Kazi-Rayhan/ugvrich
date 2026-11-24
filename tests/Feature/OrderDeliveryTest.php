<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Client;
use App\Models\ConsultancyApplication as Application;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\ExpertWallet;
use App\Services\OrderDelivery;
use App\Support\Sales\PaymentRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_expert_team_can_work_through_milestones_and_split_confirmed_earnings(): void
    {
        $admin = User::factory()->create();
        $category = ServiceCategory::create(['name' => 'Advisory', 'slug' => 'advisory']);
        $applications = User::factory()->count(3)->create(['role' => User::EXPERT])->map(fn (User $user) => Application::create([
            'user_id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'phone' => '1234567', 'location' => 'Barishal', 'occupation' => 'Consultant',
            'service_category_id' => $category->id, 'education' => [], 'skills' => [],
            'status' => Application::APPROVED, 'signed_at' => now(), 'engagement_percentage' => 50,
        ]));
        [$first, $second, $other] = $applications->all();
        $order = Order::create([
            'client_id' => Client::create(['name' => 'Example Client'])->id,
            'order_date' => now()->toDateString(),
            'status' => 'confirmed',
        ]);
        $workOrder = WorkOrder::create([
            'sector' => 'ict',
            'details' => [
                'summary' => 'Expert-visible scope',
                'fees' => [['rate' => '98765.43']],
                'payment_due' => 'Private payment instructions',
            ],
        ]);
        $order->update(['work_order_id' => $workOrder->id]);
        $order->items()->create(['service_name' => 'Advisory', 'unit_price' => 3000]);
        $order->recalculate();

        $this->actingAs($admin);
        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->assertActionVisible('assignExperts')
            ->callAction('assignExperts', data: ['experts' => [$first->id, $second->id]])
            ->assertHasNoActionErrors()
            ->callAction('planMilestones', data: ['milestones' => [
                ['title' => 'Discovery', 'tasks' => [['title' => 'Review intake']]],
                ['title' => 'Delivery', 'tasks' => [['title' => 'Prepare findings']]],
            ]])
            ->assertHasNoActionErrors()
            ->assertSee('0 of 2 complete');

        [$discovery, $delivery] = $order->fresh()->delivery_milestones;
        $task = $discovery['tasks'][0];
        $move = route('expert.orders.tasks.update', [$order, $discovery['id'], $task['id']]);
        $this->actingAs(User::findOrFail($other->user_id))->patchJson($move, ['status' => 'done'])->assertNotFound();
        $this->actingAs(User::findOrFail($first->user_id))
            ->get('/en/expert/dashboard/assignments')
            ->assertOk()
            ->assertSeeInOrder(['Work order', 'Milestones', 'Actions', $workOrder->number, 'Show', 'Tasks'])
            ->assertDontSee('Review intake');
        $this->get(route('expert.orders.show', $order))
            ->assertOk()
            ->assertSee('Expert-visible scope')
            ->assertDontSee('98765')
            ->assertDontSee('Private payment instructions')
            ->assertDontSee('Unit price');
        $this->get(route('expert.orders.tasks', $order))
            ->assertOk()
            ->assertSeeInOrder(['Discovery', 'To do', 'Review intake', 'In progress', 'Done'])
            ->assertSee('draggable="true"', escape: false);
        $this->actingAs(User::findOrFail($other->user_id))->get(route('expert.orders.show', $order))->assertNotFound();
        $this->get(route('expert.orders.tasks', $order))->assertNotFound();
        $this->actingAs(User::findOrFail($first->user_id));
        $this->patchJson($move, ['status' => 'doing'])->assertOk()->assertJsonPath('percent', 0);
        $this->assertSame('doing', $order->fresh()->delivery_milestones[0]['tasks'][0]['status']);
        $this->patchJson($move, ['status' => 'done'])->assertOk()->assertJsonPath('percent', 50)->assertJsonPath('completed', 0);
        $this->get(route('expert.orders.index'))->assertOk()->assertSee('value="50"', escape: false);
        $this->get(route('expert.orders.show', $order))->assertOk()->assertSee('value="50"', escape: false);

        $this->actingAs($admin);
        $paymentData = ['paid_on' => now()->toDateString(), 'method' => array_key_first(Payment::METHODS)];
        PaymentRecorder::record($order, $paymentData + ['amount' => '2000', 'status' => Payment::CONFIRMED], $admin);
        PaymentRecorder::record($order, $paymentData + ['amount' => '1000', 'status' => Payment::PENDING], $admin);

        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->callAction('completeMilestone', data: ['milestone' => $delivery['id']])
            ->assertHasNoActionErrors()
            ->assertSee('1 of 2 complete')
            ->callAction('releaseExpertEarnings')->assertHasNoActionErrors();

        $this->actingAs(User::findOrFail($first->user_id))->get('/en/expert/dashboard')
            ->assertOk()
            ->assertSee('Tasks by status')
            ->assertSee('1 of 2 milestones completed')
            ->assertSee('value="100"', escape: false)
            ->assertSee($order->number);
        $this->actingAs(User::findOrFail($other->user_id))->get('/en/expert/dashboard')
            ->assertOk()
            ->assertSee('No tasks have been scheduled yet.')
            ->assertDontSee($order->number);
        $this->actingAs($admin);

        $wallet = app(ExpertWallet::class);
        $this->assertSame(25000, $wallet->balances($first)['available']);
        $this->assertSame(25000, $wallet->balances($second)['available']);
        $this->assertSame(0, $wallet->balances($other)['available']);

        $service = app(OrderDelivery::class);
        $this->assertSame(0, $service->release($order, $admin));
        $service->complete($order, $admin, $discovery['id']);
        $this->assertSame(50000, $service->release($order, $admin));
        $this->assertSame(50000, $wallet->balances($first)['available']);
        $this->assertSame(50000, $wallet->balances($second)['available']);
        $this->actingAs(User::findOrFail($first->user_id))
            ->patchJson($move, ['status' => 'todo'])->assertUnprocessable();
    }
}
