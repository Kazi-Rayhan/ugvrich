<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Proposal;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\Money;
use App\Support\Sales\WorkOrderConfirmation;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesDocumentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_work_order_can_be_confirmed_after_it_has_a_client_and_invoiced_after_payment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::withQueryParams(['draft' => 1])->test(CreateWorkOrder::class)
            ->call('create')
            ->assertHasNoErrors();

        $workOrder = WorkOrder::sole();
        $this->assertNull($workOrder->sector);
        $this->assertNull($workOrder->issue_date);
        $this->assertNull($workOrder->client_id);

        Livewire::test(EditWorkOrder::class, ['record' => $workOrder->id])
            ->callAction('confirmWorkOrder')
            ->assertNotified('Add a client before confirming this work order.');
        $this->assertDatabaseCount('orders', 0);

        $workOrder->update(['details' => [
            'client' => ['name' => 'Invoice client'],
            'fees' => [['qty' => 2, 'rate' => 50]],
        ]]);

        Livewire::test(EditWorkOrder::class, ['record' => $workOrder->id])
            ->callAction('acceptWorkOrder');
        $this->assertSame('accepted', $workOrder->fresh()->status);
        $this->assertDatabaseCount('orders', 0);

        $edit = Livewire::test(EditWorkOrder::class, ['record' => $workOrder->id])
            ->callAction('confirmWorkOrder');
        $order = Order::sole();
        $edit->assertRedirect(OrderResource::getUrl('view', ['record' => $order]));
        $this->assertSame('confirmed', $workOrder->fresh()->status);
        $this->assertSame('confirmed', $order->status);
        $this->assertEquals($workOrder->id, $order->work_order_id);
        $this->assertSame($order->id, WorkOrderConfirmation::confirm($workOrder, $user)->id);
        $this->assertDatabaseCount('orders', 1);

        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->callAction('recordPayment', data: [
                'amount' => 25,
                'paid_on' => now()->toDateString(),
                'method' => array_key_first(Payment::METHODS),
                'transaction_id' => 'INVOICE-TEST-25',
                'status' => Payment::CONFIRMED,
            ])->assertHasNoActionErrors();

        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->callAction('recordPayment', data: [
                'amount' => 10,
                'paid_on' => now()->toDateString(),
                'method' => array_key_first(Payment::METHODS),
                'transaction_id' => 'PENDING-TEST-10',
                'status' => Payment::PENDING,
            ])->assertHasNoActionErrors();

        $order->refresh();
        $this->assertSame(7500, $order->outstandingMinor());
        $orderUrl = OrderResource::getUrl('view', ['record' => $order]);
        $this->get($orderUrl)
            ->assertOk()
            ->assertSeeInOrder(['Payment summary', 'Balance due', 'Overview', 'Payment history', 'Order details', 'Itemized charges'])
            ->assertSee(Money::format($order->amount_outstanding))
            ->assertSee('awaiting confirmation');
        $this->get($orderUrl.'?relation=payments')
            ->assertOk()
            ->assertSee('Balance due')
            ->assertSee('Payment history');
        $this->get(route('sales.orders.invoice', $order))
            ->assertOk()
            ->assertSee('Invoice client')
            ->assertSee('INVOICE-TEST-25')
            ->assertDontSee('PENDING-TEST-10')
            ->assertSee(Money::format($order->amount_outstanding))
            ->assertSee('Balance due')
            ->assertSee('<svg', escape: false);
    }

    public function test_first_party_can_add_a_new_client_when_creating_a_work_order(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ServiceCategory::create(['name' => 'Consultancy', 'slug' => 'smart-ict-services']);

        $form = Livewire::withQueryParams(['work_order' => 1])->test(CreateProposal::class)
            ->set('data.work_order.client_mode', 'new')
            ->fillForm([
                'title' => 'Site assessment',
                'issue_date' => '2026-11-20',
                'valid_until' => '2026-12-20',
                'work_order' => [
                    'sector' => array_key_first(WorkOrder::sectorOptions()),
                    'new_client' => [
                        'name' => 'New contact',
                        'organization' => 'New organization',
                        'email' => 'first-party@example.test',
                        'address' => 'Barishal',
                    ],
                ],
            ])
            ->set('data.work_order.details.services', ['website']);
        $itemKey = array_key_first($form->get('data.items'));
        $form->set("data.items.{$itemKey}.unit_price", 100)
            ->set("data.items.{$itemKey}.discount", '')
            ->set('data.extra_discount', '')
            ->set('data.tax_rate', '')
            ->call('create')->assertHasNoErrors();
        $this->assertTrue(Proposal::sole()->categories()->whereKey($category->id)->exists());

        $client = Client::sole();
        $workOrder = WorkOrder::sole();

        $proposal = Proposal::sole();
        $this->assertEquals(0, $proposal->extra_discount);
        $this->assertEquals(0, $proposal->tax_rate);
        $this->assertEquals(0, $proposal->items()->sole()->discount);
        $this->assertEquals($client->id, Proposal::sole()->client_id);
        $this->assertEquals($client->id, $workOrder->client_id);
        $this->assertSame('New organization', $workOrder->client_name);
        $this->assertSame('Barishal', $workOrder->detail('client.address'));
    }

    public function test_work_order_print_omits_empty_rows_and_unselected_options(): void
    {
        $this->actingAs(User::factory()->create());
        $workOrder = WorkOrder::create([
            'sector' => 'ict',
            'details' => [
                'services' => ['website'],
                'coordinator' => 'Project coordinator',
                'info' => ['users' => '125'],
                'stage_dates' => [1 => '2026-12-01'],
            ],
        ]);
        $template = WorkOrder::sector('ict');

        $this->get(route('sales.work-orders.print', $workOrder))
            ->assertOk()
            ->assertSee('Project coordinator')
            ->assertSee($template['services']['website'])
            ->assertSee($template['stages'][1][0])
            ->assertDontSee($template['services']['portal'])
            ->assertDontSee($template['info'][0][0])
            ->assertDontSee($template['stages'][0][0])
            ->assertDontSee($template['attachments'][array_key_first($template['attachments'])]);
    }

    public function test_shared_form_saves_linked_drafts_from_one_set_of_details(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/work-orders')->assertOk()->assertSee('/admin/proposals/create?work_order=1')->assertDontSee('Proposals & Work Orders');
        $this->get('/admin/work-orders/create')->assertRedirect('/admin/proposals/create?work_order=1');
        $this->get('/admin/proposals/create?work_order=1')
            ->assertOk()
            ->assertSeeInOrder(['Work order', 'Scope & schedule', 'Payments', 'Work order review'])
            ->assertSee('The category determines the available subcategories')
            ->assertDontSee('Responsible department')
            ->assertDontSee('fi-tabs-item-label', escape: false);
        $client = Client::create(['name' => 'Ayesha Rahman', 'organization' => 'Example Client']);
        $category = ServiceCategory::create(['name' => 'Consultancy', 'slug' => 'smart-ict-services']);
        $sector = array_key_first(WorkOrder::sectorOptions());

        $form = Livewire::withQueryParams(['work_order' => 1])->test(CreateProposal::class)
            ->assertSet('startsFromWorkOrders', true)
            ->assertSet('data.work_order.client_mode', 'existing')
            ->set('data.work_order.client_id', $client->id)
            ->assertSet('data.work_order.details.client.name', 'Example Client')
            ->fillForm([
                'title' => 'Site assessment',
                'issue_date' => '2026-11-20',
                'valid_until' => '2026-12-20',
                'payment_terms' => 'Payment on delivery',
            ])
            ->call('create')
            ->assertHasErrors(['data.work_order.sector']);

        $this->assertDatabaseCount('proposals', 0);
        $this->assertDatabaseCount('work_orders', 0);
        $this->assertSame(
            ['Work order', 'Scope & schedule', 'Payments', 'Work order review', 'Pricing', 'Scope & conditions', 'Terms & review'],
            array_map(fn ($step) => $step->getLabel(), $form->instance()->getSteps()),
        );

        $form->set('data.work_order.sector', 'ict')
            ->assertSee('Subcategories')
            ->set('data.work_order.details.services', ['website'])
            ->set('data.work_order.sector', 'eee')
            ->assertSet('data.work_order.details.services', [])
            ->assertSet('data.items', []);

        $form->fillForm([
            'work_order' => [
                'sector' => $sector,
                'details' => [
                    'coordinator' => 'Project coordinator',
                    'start_date' => '2026-12-01',
                    'end_date' => '2026-12-12',
                    'rich_signatory' => 'Office lead',
                    'installments' => [['percent' => 65]],
                ],
            ],
        ]);

        $form->set('data.work_order.sector', $sector)
            ->set('data.work_order.details.services', ['website']);

        $itemKey = array_key_first($form->get('data.items'));
        $form->assertSet("data.items.{$itemKey}.service_name", 'Institutional website development')
            ->set("data.items.{$itemKey}.quantity", 2)
            ->set("data.items.{$itemKey}.unit_price", 100)
            ->set("data.items.{$itemKey}.discount", 10)
            ->set('data.work_order.details.services', ['website', 'portal'])
            ->assertSet("data.items.{$itemKey}.unit_price", 100)
            ->set('data.work_order.details.services', ['website']);
        $form->call('create')->assertHasNoErrors();
        $proposal = Proposal::sole();
        $workOrder = WorkOrder::sole();

        $this->assertEquals([$category->id], $proposal->categories()->pluck('service_categories.id')->all());
        $this->assertSame($proposal->id, $workOrder->proposal_id);
        $this->assertSame($client->id, $workOrder->client_id);
        $this->assertSame('Example Client', $workOrder->client_name);
        $this->assertSame('draft', $workOrder->status);
        $this->assertSame($sector, $workOrder->sector);
        $this->assertSame('2026-11-20', $workOrder->issue_date->toDateString());
        $this->assertSame(19000, Money::toMinor($workOrder->total));
        $this->assertSame($proposal->number, $workOrder->detail('quotation_number'));
        $this->assertStringContainsString('Institutional website development', $workOrder->detail('included'));
        $this->assertSame('Payment on delivery', $workOrder->detail('payment_due'));
        $this->assertSame('Project coordinator', $workOrder->detail('coordinator'));
        $this->assertSame('2026-12-12', $workOrder->detail('end_date'));
        $this->assertSame('Office lead', $workOrder->detail('rich_signatory'));
        $this->assertEquals(65, $workOrder->detail('installments.0.percent'));
        $this->assertEquals(30, $workOrder->detail('installments.1.percent'));
        $editUrl = '/admin/proposals/'.$proposal->id.'/edit?work_order='.$workOrder->id;
        $form->assertRedirect($editUrl);
        $this->get('/admin/work-orders')->assertOk()->assertSee($editUrl);
        $this->get($editUrl)->assertOk()->assertSee('Work order PDF')->assertDontSee('07 Fees and payment approval');
        $this->get(WorkOrderResource::getUrl('edit', ['record' => $workOrder]))
            ->assertOk()
            ->assertSeeInOrder(['Work order', 'Scope & schedule', 'Payments', 'Work order review'])
            ->assertSeeInOrder(['Contracting parties', 'Work order template', 'Installments and payment terms'])
            ->assertDontSee('01-02 Parties and services');

        $editPage = $this->get('/admin/proposals/'.$proposal->id.'/edit')->assertOk();
        $this->assertSame(1, substr_count($editPage->getContent(), 'role="tablist"'));
        $editPage->assertSeeInOrder(['Work order', 'Services &amp; pricing'], escape: false)
            ->assertSee('Document details')
            ->assertSee('Proposal title')
            ->assertDontSee('07 Fees and payment approval')
            ->assertSee('Installments and payment terms');
        $this->assertDoesNotMatchRegularExpression('/class="fi-tabs-item-label">\s*(Client|Proposal)\s*<\/span>/', $editPage->getContent());
        $editPage->assertSee('Work order template')
            ->assertSee('First Party (Client)')
            ->assertSee('Second Party (Service Provider)')
            ->assertSee('Consultancy services')
            ->assertSee('Institutional website development')
            ->assertSee('Deployment environment')
            ->assertSee('Requirements assessment')
            ->assertSee('Approved requirements / statement of work')
            ->assertSee('Pre-issue checklist')
            ->assertSee('Bangla print previews');

        $workOrder->update(['status' => 'issued']);

        Livewire::test(EditProposal::class, ['record' => $proposal->id])
            ->assertSet('data.work_order.details.client.name', 'Example Client')
            ->assertSet('data.work_order.details.coordinator', 'Project coordinator')
            ->callAction(TestAction::make('createOption')->schemaComponent('work_order.client_id', 'form'), data: [
                'name' => 'New contact',
                'organization' => 'New organization',
                'email' => 'new-client@example.test',
            ])
            ->assertHasNoErrors()
            ->assertSet('data.work_order.details.client.name', 'New organization')
            ->fillForm([
                'work_order.details.client.contact' => 'New client contact',
                'work_order.details.coordinator' => 'New coordinator',
                'title' => 'Updated site assessment',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('work_orders', 1);
        $newClient = Client::where('email', 'new-client@example.test')->sole();
        $this->assertEquals($newClient->id, $proposal->fresh()->client_id);
        $this->assertSame('Updated site assessment', $proposal->fresh()->title);
        $this->assertEquals($newClient->id, $workOrder->fresh()->client_id);
        $this->assertSame('New coordinator', $workOrder->fresh()->detail('coordinator'));
        $this->assertSame('New client contact', $workOrder->fresh()->detail('client.contact'));
        $this->assertSame('issued', $workOrder->fresh()->status);
        $this->assertSame(19000, Money::toMinor($workOrder->fresh()->total));
        $this->assertSame('2026-12-12', $workOrder->fresh()->detail('end_date'));

        Livewire::test(EditProposal::class, ['record' => $proposal->id])
            ->callAction('confirmWorkOrder');
        $order = Order::sole();
        $this->assertEquals($proposal->id, $order->proposal_id);
        $this->assertEquals($workOrder->id, $order->work_order_id);
        $this->assertSame('converted', $proposal->fresh()->status);
        $this->assertSame(19000, Money::toMinor($order->total));
    }
}
