<?php

namespace Tests\Feature;

use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\Money;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesDocumentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_party_can_add_a_new_client_when_creating_a_work_order(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ServiceCategory::create(['name' => 'Consultancy', 'slug' => 'test-consultancy']);

        Livewire::withQueryParams(['work_order' => 1])->test(CreateProposal::class)
            ->set('data.work_order.client_mode', 'new')
            ->fillForm([
                'title' => 'Site assessment',
                'issue_date' => '2026-11-20',
                'valid_until' => '2026-12-20',
                'categories' => [$category->id],
                'items' => [['service_name' => 'Site visit', 'quantity' => 1, 'unit_price' => 100, 'discount' => 0]],
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
            ->call('create')
            ->assertHasNoErrors();

        $client = Client::sole();
        $workOrder = WorkOrder::sole();

        $this->assertEquals($client->id, Proposal::sole()->client_id);
        $this->assertEquals($client->id, $workOrder->client_id);
        $this->assertSame('New organization', $workOrder->client_name);
        $this->assertSame('Barishal', $workOrder->detail('client.address'));
    }

    public function test_shared_form_saves_linked_drafts_from_one_set_of_details(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/work-orders')->assertOk()->assertSee('/admin/proposals/create?work_order=1')->assertDontSee('Proposals & Work Orders');
        $this->get('/admin/work-orders/create')->assertRedirect('/admin/proposals/create?work_order=1');
        $this->get('/admin/proposals/create?work_order=1')->assertOk()->assertSee('Linked work order');
        $client = Client::create(['name' => 'Ayesha Rahman', 'organization' => 'Example Client']);
        $category = ServiceCategory::create(['name' => 'Consultancy', 'slug' => 'test-consultancy']);
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
                'categories' => [$category->id],
                'items' => [['service_name' => 'Site visit', 'quantity' => 2, 'unit_price' => 100, 'discount' => 10]],
                'payment_terms' => 'Payment on delivery',
            ])
            ->call('create')
            ->assertHasErrors(['data.work_order.sector']);

        $this->assertDatabaseCount('proposals', 0);
        $this->assertDatabaseCount('work_orders', 0);
        $this->assertSame(
            ['Work order', 'Services & pricing', 'Scope & conditions', 'Terms & review'],
            array_map(fn ($step) => $step->getLabel(), $form->instance()->getSteps()),
        );

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
        ])->call('create')->assertHasNoErrors();
        $proposal = Proposal::sole();
        $workOrder = WorkOrder::sole();

        $this->assertSame($proposal->id, $workOrder->proposal_id);
        $this->assertSame($client->id, $workOrder->client_id);
        $this->assertSame('Example Client', $workOrder->client_name);
        $this->assertSame('draft', $workOrder->status);
        $this->assertSame($sector, $workOrder->sector);
        $this->assertSame('2026-11-20', $workOrder->issue_date->toDateString());
        $this->assertSame(19000, Money::toMinor($workOrder->total));
        $this->assertSame($proposal->number, $workOrder->detail('quotation_number'));
        $this->assertStringContainsString('Site visit', $workOrder->detail('included'));
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
            ->assertSeeInOrder(['Contracting parties', 'Work order template', 'Installments and payment terms'])
            ->assertDontSee('01-02 Parties and services');

        $editPage = $this->get('/admin/proposals/'.$proposal->id.'/edit')->assertOk();
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
    }
}
