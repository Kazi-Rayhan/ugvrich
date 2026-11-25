<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Models\Client;
use App\Models\FinanceAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Proposal;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\ClientPortalWelcome;
use App\Support\Money;
use App\Support\Sales\PaymentRecorder;
use App\Support\Sales\WorkOrderConfirmation;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SalesDocumentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_lists_show_delete_buttons_for_eligible_and_protected_records(): void
    {
        $this->actingAs(User::factory()->create());

        $unusedClient = Client::create(['name' => 'Unused client']);
        $linkedClient = Client::create(['name' => 'Linked client']);
        $draftWorkOrder = WorkOrder::create(['sector' => 'ict', 'status' => 'draft']);
        $confirmedWorkOrder = WorkOrder::create(['sector' => 'ict', 'status' => 'confirmed']);
        $standaloneOrder = Order::create(['client_id' => $linkedClient->id, 'order_date' => today()]);
        $linkedOrder = Order::create([
            'client_id' => $linkedClient->id,
            'order_date' => today(),
            'work_order_id' => $confirmedWorkOrder->id,
        ]);

        foreach ([
            [ListClients::class, $unusedClient, $linkedClient],
            [ListOrders::class, $standaloneOrder, $linkedOrder],
            [ListWorkOrders::class, $draftWorkOrder, $confirmedWorkOrder],
        ] as [$page, $eligible, $protected]) {
            $eligibleDelete = TestAction::make('delete')->table($eligible);
            $protectedDelete = TestAction::make('delete')->table($protected);

            Livewire::test($page)
                ->assertActionVisible($eligibleDelete)
                ->assertActionEnabled($eligibleDelete)
                ->assertActionVisible($protectedDelete)
                ->assertActionDisabled($protectedDelete);
        }
    }

    public function test_client_edit_can_enable_portal_access_and_replace_a_password(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['name' => 'Portal client', 'email' => 'portal-client@example.test']);

        $this->get('/admin/clients/'.$client->id.'/edit')
            ->assertOk()
            ->assertSee('/en/client/dashboard', escape: false)
            ->assertSee('New portal password');

        Livewire::test(EditClient::class, ['record' => $client->id])
            ->fillForm(['portal_password' => 'initial-portal-pass'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('data.portal_password', null);
        $user = $client->fresh()->portalUser;
        $this->assertSame(User::CLIENT, $user->role);
        $this->assertTrue(Hash::check('initial-portal-pass', $user->password));

        Livewire::test(EditClient::class, ['record' => $client->id])
            ->fillForm(['portal_password' => 'changed-portal-pass'])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame($user->id, $client->fresh()->user_id);
        $this->assertTrue(Hash::check('changed-portal-pass', $user->fresh()->password));
    }

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

        $account = FinanceAccount::create(['name' => 'Main receiving account', 'type' => 'bank', 'is_active' => true]);

        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->callAction('recordPayment', data: [
                'amount' => 25,
                'paid_on' => now()->toDateString(),
                'finance_account_id' => $account->id,
                'transaction_id' => 'INVOICE-TEST-25',
                'status' => Payment::CONFIRMED,
            ])->assertHasNoActionErrors();

        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->callAction('recordPayment', data: [
                'amount' => 10,
                'paid_on' => now()->toDateString(),
                'finance_account_id' => $account->id,
                'transaction_id' => 'PENDING-TEST-10',
                'status' => Payment::PENDING,
            ])->assertHasNoActionErrors();

        $order->refresh();
        $confirmed = $order->payments()->where('status', Payment::CONFIRMED)->sole();
        $this->assertEquals($account->id, $confirmed->finance_account_id);
        $this->assertSame(2500, $account->refresh()->balance_minor);
        $this->assertSame(2500, $confirmed->financeTransaction->amount_minor);
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
            ->assertSee('Main receiving account')
            ->assertDontSee('PENDING-TEST-10')
            ->assertSee(Money::format($order->amount_outstanding))
            ->assertSee('Balance due')
            ->assertSee('<svg', escape: false);

        $pending = $order->payments()->where('status', Payment::PENDING)->sole();
        PaymentRecorder::confirm($pending, $user);
        $this->assertSame(3500, $account->refresh()->balance_minor);
        PaymentRecorder::reverse($confirmed, 'Payment refunded', $user);
        $this->assertSame(1000, $account->refresh()->balance_minor);
        $this->assertNotNull($confirmed->financeTransaction->reversal()->first());

        $historical = $order->payments()->create([
            'status' => Payment::CONFIRMED, 'method' => 'account', 'amount' => 5,
            'paid_on' => now()->toDateString(), 'finance_account_id' => $account->id,
        ]);
        PaymentRecorder::postExisting($historical, $user);
        PaymentRecorder::postExisting($historical, $user);
        $this->assertSame(1500, $account->fresh()->balance_minor);
    }

    public function test_first_party_can_add_a_new_client_when_creating_a_work_order(): void
    {
        Notification::fake();
        $this->actingAs(User::factory()->create());
        $category = ServiceCategory::create([
            'name' => 'Consultancy',
            'slug' => 'smart-ict-services',
            'proposal_conditions' => [['label' => 'Preset clause', 'details' => 'Default terms from the category.']],
        ]);

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
            ->set('data.work_order.details.services', ['website'])
            ->assertSee('30 days')
            ->set('data.valid_until', '2026-12-11')
            ->assertSee('21 days');
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
        $this->assertSame('21 days', $proposal->delivery_timeline);
        $this->assertEquals(0, $proposal->extra_discount);
        $this->assertEquals(0, $proposal->tax_rate);
        $this->assertEquals(0, $proposal->items()->sole()->discount);
        $this->assertEquals($client->id, Proposal::sole()->client_id);
        $this->assertEquals($client->id, $workOrder->client_id);
        $this->assertSame('New organization', $workOrder->client_name);
        $this->assertSame('Barishal', $workOrder->detail('client.address'));

        $user = User::findOrFail($client->user_id);
        $this->assertSame(User::CLIENT, $user->role);
        $this->assertSame($client->email, $user->email);
        Notification::assertSentTo($user, ClientPortalWelcome::class);
        $url = Notification::sent($user, ClientPortalWelcome::class)->sole()->toMail($user)->actionUrl;

        auth()->logout();
        $this->post(route('en.password.store'), [
            'token' => basename(parse_url($url, PHP_URL_PATH)),
            'email' => $user->email,
            'password' => 'initial-client-pass',
            'password_confirmation' => 'initial-client-pass',
        ])->assertRedirect('/en/client/dashboard');
        $this->assertAuthenticatedAs($user);

        $order = $client->orders()->create(['status' => 'confirmed', 'order_date' => now(), 'notes' => 'Private staff note']);
        $order->items()->create(['service_name' => 'Site assessment', 'unit_price' => 100]);
        $order->recalculate();
        $otherOrder = Client::create(['name' => 'Another client'])->orders()->create(['status' => 'confirmed', 'order_date' => now()]);

        $this->get(route('client.dashboard'))->assertOk()
            ->assertSee('assets/client-portal-', escape: false)
            ->assertSee('width="17" height="17"', escape: false)
            ->assertSee('Welcome, New contact')
            ->assertSee($order->number)->assertDontSee($otherOrder->number);
        $this->get(route('client.orders'))->assertOk()->assertSee('section-intro--orders')->assertSee($order->number);
        $this->get(route('client.orders.show', $order))->assertOk()->assertSee('Print invoice')
            ->assertSee('Site assessment')->assertDontSee('Private staff note');
        $this->get(route('client.orders.invoice', $order))->assertOk()->assertSee('Site assessment');
        $this->get(route('client.orders.show', $otherOrder))->assertNotFound();
        $this->get(route('client.orders.invoice', $otherOrder))->assertNotFound();
        $this->get('/admin')->assertForbidden();

        $this->get(route('client.profile'))->assertOk()->assertSee('section-intro--profile')->assertSee('Billing address');
        $this->put(route('client.profile.update'), ['name' => 'Updated client', 'address' => 'Dhaka'])->assertSessionHasNoErrors();
        $this->assertSame('Dhaka', $client->fresh()->address);
        $this->get(route('client.security'))->assertOk()->assertSee('section-intro--security')->assertSee('Change password');
        $this->put(route('client.security.update'), [
            'current_password' => 'initial-client-pass',
            'password' => 'changed-client-pass',
            'password_confirmation' => 'changed-client-pass',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('changed-client-pass', $user->fresh()->password));
        $this->post(route('client.logout'))->assertRedirect(route('login'));
        $this->assertGuest();
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
            ->assertDontSee('সেবাভিত্তিক বিশেষ শর্ত')
            ->assertDontSee('সাধারণ প্রশাসন ও বিরোধ নিষ্পত্তি')
            ->assertDontSee($template['services']['portal'])
            ->assertDontSee($template['info'][0][0])
            ->assertDontSee($template['stages'][0][0])
            ->assertDontSee($template['attachments'][array_key_first($template['attachments'])]);

        $electricalWorkOrder = WorkOrder::create([
            'sector' => 'eee',
            'details' => ['info' => ['standards_apply' => true]],
        ]);
        $this->get(route('sales.work-orders.print', $electricalWorkOrder))
            ->assertOk()
            ->assertSee('বিদ্যমান SLD, পরিচালন তথ্য, BNBC ও প্রযোজ্য মান');
    }

    public function test_shared_form_saves_linked_drafts_from_one_set_of_details(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/work-orders')->assertOk()->assertSee('/admin/proposals/create?work_order=1')
            ->assertDontSee('Proposals & Work Orders')->assertDontSee('New blank draft');
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
            ])
            ->call('create')
            ->assertHasErrors(['data.work_order.sector']);

        $this->assertDatabaseCount('proposals', 0);
        $this->assertDatabaseCount('work_orders', 0);
        $this->assertSame(
            ['Work order', 'Scope & schedule', 'Payments', 'Work order review', 'Pricing', 'Terms & review'],
            array_map(fn ($step) => $step->getLabel(), $form->instance()->getSteps()),
        );

        $form->set('data.work_order.sector', 'ict')
            ->assertSee('Subcategories')
            ->set('data.work_order.details.services', ['website'])
            ->set('data.work_order.sector', 'eee')
            ->assertSee('Existing single-line diagrams, operating data, BNBC, and applicable standards')
            ->assertDontSee('Technical basis notes')
            ->assertSet('data.work_order.details.services', [])
            ->assertSet('data.items', []);

        $form->fillForm([
            'work_order' => [
                'sector' => $sector,
                'details' => [
                    'coordinator' => 'Project coordinator',
                    'mode' => 'online',
                    'rich_signatory' => 'Office lead',
                    'tech_approval_status' => 'no',
                    'installments' => [['percent' => 65]],
                ],
            ],
        ]);

        $form->set('data.work_order.sector', $sector)
            ->set('data.work_order.details.services', ['website']);
        $milestoneAction = TestAction::make('add')->schemaComponent('work_order.details.milestones', 'form');
        $form->callAction($milestoneAction)->callAction($milestoneAction);
        $milestones = $form->get('data.work_order.details.milestones');
        [$pilotKey, $handoverKey] = array_keys($milestones);
        $milestoneId = $milestones[$pilotKey]['id'];
        $form->set("data.work_order.details.milestones.{$pilotKey}.title", 'Pilot review')
            ->set("data.work_order.details.milestones.{$pilotKey}.date", '2026-12-08')
            ->set("data.work_order.details.milestones.{$handoverKey}.title", 'Client handover')
            ->set("data.work_order.details.milestones.{$handoverKey}.date", '2026-12-15');
        $form->set('data.work_order.details.info.languages', ['Bangla', 'English', 'Japanese'])
            ->set('data.work_order.details.info.platform_web', true)
            ->set('data.work_order.details.info.platform_mobile', true)
            ->set('data.work_order.details.interim_milestone', $milestoneId);

        $addPricingLine = TestAction::make('add')->schemaComponent('items', 'form');
        $form->assertSee('Add more')
            ->callAction($addPricingLine)
            ->assertSet('data.work_order.details.services', ['website'])
            ->callAction($addPricingLine);
        [, $customKey, $secondCustomKey] = array_keys($form->get('data.items'));
        $form->assertSet("data.items.{$customKey}.service_name", null)
            ->set("data.items.{$customKey}.service_name", 'External battery')
            ->set("data.items.{$secondCustomKey}.service_name", 'Installation kit')
            ->set("data.items.{$customKey}.unit_price", 0)
            ->set("data.items.{$customKey}.description", 'Custom site assessment')
            ->set('data.work_order.details.services', [])
            ->assertSet("data.items.{$customKey}.description", 'Custom site assessment')
            ->assertSet("data.items.{$secondCustomKey}.service_name", 'Installation kit')
            ->callAction(TestAction::make('delete')->schemaComponent('items', 'form'), arguments: ['item' => $secondCustomKey])
            ->set('data.work_order.details.services', ['website']);
        $itemKey = array_key_first($form->get('data.items'));
        $form->assertSet("data.items.{$itemKey}.service_name", 'Institutional website development')
            ->set("data.items.{$itemKey}.quantity", 2)
            ->set("data.items.{$itemKey}.unit_price", 100)
            ->set("data.items.{$itemKey}.discount", 10)
            ->set('data.work_order.details.services', ['website', 'portal'])
            ->assertSet("data.items.{$itemKey}.unit_price", 100)
            ->set('data.work_order.details.services', ['website']);
        $form->assertDontSee('Reload templates')
            ->assertSet('data.conditions', [])
            ->assertDontSee('Terms and conditions')
            ->call('create')->assertHasNoErrors();
        $proposal = Proposal::sole();
        $this->assertEmpty($proposal->conditions);
        $workOrder = WorkOrder::sole();
        $customItem = $proposal->items()->where('is_custom', true)->sole();
        $this->assertSame('External battery', $customItem->service_name);
        $this->assertSame('Custom site assessment', $customItem->description);
        $this->assertEquals($category->id, $customItem->service_category_id);

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
        $this->assertNull($proposal->payment_terms);
        $this->assertSame('Project coordinator', $workOrder->detail('coordinator'));
        $this->assertSame('2026-11-20', $workOrder->detail('start_date'));
        $this->assertSame('2026-12-20', $workOrder->detail('end_date'));
        $this->assertSame([
            ['title' => 'Pilot review', 'date' => '2026-12-08'],
            ['title' => 'Client handover', 'date' => '2026-12-15'],
        ], array_map(fn (array $milestone) => array_diff_key($milestone, ['id' => true]), $workOrder->detail('milestones')));
        $this->assertSame('online', $workOrder->detail('mode'));
        $this->assertSame('Bangla, English, Japanese', $workOrder->detail('info.languages'));
        $this->assertTrue($workOrder->detail('info.platform_web'));
        $this->assertTrue($workOrder->detail('info.platform_mobile'));
        $this->assertSame('Office lead', $workOrder->detail('rich_signatory'));
        $this->assertSame('no', $workOrder->detail('tech_approval_status'));
        $this->assertEquals(65, $workOrder->detail('installments.0.percent'));
        $this->assertEquals(30, $workOrder->detail('installments.1.percent'));
        $this->assertSame($milestoneId, $workOrder->detail('interim_milestone'));
        $editUrl = '/admin/proposals/'.$proposal->id.'/edit?work_order='.$workOrder->id;
        $form->assertRedirect($editUrl);
        $this->get(route('sales.work-orders.print', $workOrder))
            ->assertOk()
            ->assertSee('External battery')
            ->assertDontSee('কাস্টম সেবা')
            ->assertSee('<i class="box on"></i>ওয়েব', escape: false)
            ->assertSee('<i class="box on"></i>মোবাইল', escape: false)
            ->assertDontSee('<i class="box on"></i>ERP', escape: false)
            ->assertSeeInOrder(['অতিরিক্ত প্রযুক্তিগত যাচাই / প্রকল্প ব্যবস্থাপকের অনুমোদন', '>না</span>'], escape: false)
            ->assertSeeInOrder(['Pilot review', '০৮ / ১২ / ২০২৬', 'Client handover', '১৫ / ১২ / ২০২৬'])
            ->assertSeeInOrder([
                config('work_orders.payment_steps.1.label'),
                'Pilot review',
            ]);
        $this->get('/admin/work-orders')->assertOk()->assertSee($editUrl);
        $this->get($editUrl)->assertOk()->assertSee('Work order PDF')->assertDontSee('07 Fees and payment approval');
        $this->get(WorkOrderResource::getUrl('edit', ['record' => $workOrder]))
            ->assertOk()
            ->assertSeeInOrder(['Work order', 'Scope & schedule', 'Payments', 'Work order review'])
            ->assertSeeInOrder(['Contracting parties', 'Work order template', 'Installments'])
            ->assertDontSee('01-02 Parties and services');

        $proposal->update(['terms' => 'Delivery dates are agreed in writing.', 'conditions' => [
            ['source' => 'category:'.$category->id, 'label' => 'Imported preset clause', 'details' => 'Template text'],
            ['source' => null, 'label' => 'Agreed delivery condition', 'details' => 'Handover on site'],
        ]]);
        $this->get(route('sales.proposals.print', $proposal))->assertOk()
            ->assertDontSee('Terms and conditions')
            ->assertDontSee('Delivery dates are agreed in writing.')
            ->assertSee('Agreed delivery condition')
            ->assertDontSee('Imported preset clause');
        $editPage = $this->get('/admin/proposals/'.$proposal->id.'/edit')->assertOk();
        $editPage->assertDontSee('Scope & conditions')->assertDontSee('Terms and conditions')->assertSee('Add a condition');
        $editPage->assertDontSeeText('Imported preset clause')->assertSeeText('Agreed delivery condition');
        $this->assertSame(1, substr_count($editPage->getContent(), 'role="tablist"'));
        $editPage->assertSeeInOrder(['Work order', 'Services &amp; pricing'], escape: false)
            ->assertSee('Document details')
            ->assertSee('Proposal title')
            ->assertDontSee('07 Fees and payment approval')
            ->assertSee('Installments')
            ->assertDontSee('Payment terms');
        $this->assertDoesNotMatchRegularExpression('/class="fi-tabs-item-label">\s*(Client|Proposal)\s*<\/span>/', $editPage->getContent());
        $editPage->assertSee('Work order template')
            ->assertSee('Add milestone')
            ->assertSee('Pilot review')
            ->assertSee('First Party (Client)')
            ->assertSee('Second Party (Service Provider)')
            ->assertSee('Consultancy services')
            ->assertSee('Institutional website development')
            ->assertSee('Deployment environment')
            ->assertDontSee('Integrations and data')
            ->assertDontSee('Domain, hosting, licensing, and equipment ownership')
            ->assertDontSee('Security baseline')
            ->assertDontSee('Requirements assessment')
            ->assertDontSee('Pre-issue checklist')
            ->assertSee('Bangla print previews');

        $workOrder->update(['status' => 'issued']);

        Livewire::test(EditProposal::class, ['record' => $proposal->id])
            ->assertSet('data.work_order.details.info.languages', ['Bangla', 'English', 'Japanese'])
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
        $this->assertSame('2026-12-20', $workOrder->fresh()->detail('end_date'));

        Livewire::test(EditProposal::class, ['record' => $proposal->id])
            ->callAction('confirmWorkOrder');
        $order = Order::sole();
        $this->assertEquals($proposal->id, $order->proposal_id);
        $this->assertEquals($workOrder->id, $order->work_order_id);
        $this->assertSame('converted', $proposal->fresh()->status);
        $this->assertSame(19000, Money::toMinor($order->total));
    }
}
