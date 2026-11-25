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

    public function test_existing_orders_show_and_prefill_saved_work_order_milestones(): void
    {
        $date = today()->addWeeks(2);
        $workOrder = WorkOrder::create(['sector' => 'ict', 'details' => [
            'milestones' => [['title' => 'Client acceptance', 'date' => $date->toDateString()]],
        ]]);
        $order = Order::create([
            'client_id' => Client::create(['name' => 'Example client'])->id,
            'order_date' => today(),
            'work_order_id' => $workOrder->id,
        ]);

        $this->actingAs(User::factory()->create());
        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->assertSee('Work order schedule')
            ->assertSee('Client acceptance')
            ->assertSee('Due '.$date->format('j M Y'))
            ->mountAction('planMilestones')
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertSee('1 complete')
            ->assertDontSee('Work order schedule');

        $milestone = $order->refresh()->delivery_milestones[0];
        $this->assertSame('Client acceptance', $milestone['title']);
        $this->assertSame($date->toDateString(), $milestone['due']);

        $order->update(['delivery_milestones' => []]);
        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->assertSee('Work order schedule')
            ->assertSee('Due '.$date->format('j M Y'));

        $workOrder->update(['details' => []]);
        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->mountAction('planMilestones')
            ->callMountedAction()
            ->assertHasNoActionErrors();
        $this->assertSame([], $order->refresh()->delivery_milestones);
    }

    public function test_staff_can_update_delivery_details_from_the_order_overview(): void
    {
        $admin = User::factory()->create(['name' => 'Delivery Manager']);
        $estimate = today()->addWeek();
        $delivery = today()->addWeeks(2);
        $workOrder = WorkOrder::create(['sector' => 'ict', 'details' => ['end_date' => $estimate->toDateString()]]);
        $order = Order::create([
            'client_id' => Client::create(['name' => 'Example client'])->id,
            'order_date' => today(),
            'work_order_id' => $workOrder->id,
        ]);

        $this->actingAs($admin);
        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->assertSee('Unassigned')
            ->assertSee($estimate->format('j M Y'))
            ->assertSee('Work order estimate')
            ->assertActionVisible('deliveryDetails')
            ->callAction('deliveryDetails', data: [
                'assigned_to' => $admin->id,
                'expected_delivery_date' => today()->subDay()->toDateString(),
            ])->assertHasActionErrors(['expected_delivery_date' => 'after_or_equal'])
            ->fillForm(['expected_delivery_date' => $delivery->toDateString()])
            ->callMountedAction()
            ->assertHasNoActionErrors()
            ->assertSee('Delivery Manager')
            ->assertSee($delivery->format('j M Y'))
            ->assertDontSee('Work order estimate');

        $this->assertEquals($admin->id, $order->refresh()->assigned_to);
        $this->assertSame($delivery->toDateString(), $order->expected_delivery_date->toDateString());
    }

    public function test_an_expert_without_a_contract_uses_an_order_specific_rate(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::EXPERT]);
        $category = ServiceCategory::create(['name' => 'Advisory', 'slug' => 'advisory']);
        $expert = Application::create([
            'user_id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'phone' => '1234567', 'location' => 'Barishal', 'occupation' => 'Consultant',
            'service_category_id' => $category->id, 'education' => [], 'skills' => [],
            'status' => Application::APPROVED,
        ]);
        $order = Order::create([
            'client_id' => Client::create(['name' => 'Example client'])->id,
            'order_date' => today(),
            'status' => 'confirmed',
        ]);
        $order->items()->create(['service_name' => 'Advisory', 'unit_price' => 100]);
        $order->recalculate();

        $this->actingAs($admin);
        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->mountAction('assignExperts')
            ->fillForm(['experts' => [$expert->id]])
            ->callMountedAction()
            ->assertHasActionErrors(["percentages.{$expert->id}" => 'required'])
            ->fillForm(['percentages' => [$expert->id => '25.50']])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame('25.50', $order->expertAssignments()->sole()->engagement_percentage);
        $this->assertNull($expert->fresh()->contract_type);
        $delivery = app(OrderDelivery::class);
        $delivery->plan($order, $admin, [['title' => 'Delivery', 'tasks' => []]]);
        $delivery->complete($order, $admin, $order->fresh()->delivery_milestones[0]['id']);
        PaymentRecorder::record($order, [
            'paid_on' => today()->toDateString(), 'method' => 'cash',
            'amount' => '100', 'status' => Payment::CONFIRMED,
        ], $admin);
        $this->assertSame(2550, $delivery->release($order, $admin));
        $this->actingAs($user)->get(route('expert.orders.show', $order))->assertOk();
        $this->get(route('expert.wallet.show'))->assertOk();
        $this->get(route('expert.profile.show'))->assertForbidden();
    }

    public function test_an_expert_team_can_work_through_milestones_and_split_confirmed_earnings(): void
    {
        $admin = User::factory()->create();
        $category = ServiceCategory::create(['name' => 'Advisory', 'slug' => 'advisory']);
        $applications = User::factory()->count(3)->create(['role' => User::EXPERT])->map(fn (User $user) => Application::create([
            'user_id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'phone' => '1234567', 'location' => 'Barishal', 'occupation' => 'Consultant',
            'service_category_id' => $category->id, 'education' => [], 'skills' => [],
            'status' => Application::APPROVED, 'signed_at' => now(), 'contract_type' => 'long_term', 'engagement_percentage' => 50,
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
            ->assertSeeInOrder(['Assigned orders', $order->number, 'Confirmed', 'Work order', $workOrder->number, '0 of 2 milestones completed', 'Details', 'Task board'])
            ->assertSee(route('expert.orders.show', $order))
            ->assertSee(route('expert.orders.tasks', $order))
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
            ->assertSee('draggable="true"', escape: false)
            ->assertSee('data-update-url="'.$move.'"', escape: false)
            ->assertSee('data-lane-tab="doing"', escape: false)
            ->assertSee('data-delivery-arc', escape: false)
            ->assertDontSee('<select', escape: false);
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
            ->assertSeeInOrder(['expert-mobile-nav"', 'Overview', 'Assignments', 'Profile', 'popovertarget="expert-mobile-menu"', 'id="expert-mobile-menu" popover', 'Portfolio'], escape: false)
            ->assertSee('id="expert-mobile-menu" popover', escape: false)
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
