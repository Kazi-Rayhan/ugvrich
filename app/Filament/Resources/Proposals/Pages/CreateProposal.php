<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\WorkOrderForm;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\Client;
use App\Models\ConsultancyRequest;
use App\Models\Proposal;
use App\Models\WorkOrder;
use App\Support\Sales\ClientResolver;
use App\Support\Sales\ClientPortalAccess;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/**
 * A new proposal, and, when the client is new, the client with it.
 *
 * Client, proposal, lines and categories are written in one database
 * transaction: if any part fails, none of it is kept.
 */
class CreateProposal extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ProposalResource::class;

    protected static ?string $title = 'New work order / proposal';

    protected ?array $workOrderData = null;

    protected ?WorkOrder $workOrder = null;

    #[Locked]
    public bool $startsFromWorkOrders = false;

    public function getSteps(): array
    {
        return ProposalResource::steps($this->startsFromWorkOrders);
    }

    protected ?bool $hasDatabaseTransactions = true;

    /** Opened from a consultancy request (?request=ID): start from what the requester sent. */
    public function mount(): void
    {
        $this->startsFromWorkOrders = WorkOrderResource::canCreate() && request()->boolean('work_order');
        parent::mount();

        $request = ConsultancyRequest::find(request()->integer('request'));

        if (! $request) {
            return;
        }

        $existing = (filled($request->email) ? Client::where('email', mb_strtolower(trim($request->email)))->first() : null)
            ?? (filled($request->phone) ? Client::where('phone_normalized', Client::normalizePhone($request->phone))->first() : null);

        $client = [
            'client_mode' => $existing ? 'existing' : 'new',
            'client_id' => $existing?->id,
            'new_client' => $existing ? [] : [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'organization_type' => in_array($request->organization, ConsultancyRequest::ORGANIZATION_TYPES, true) ? $request->organization : null,
                'organization' => in_array($request->organization, ConsultancyRequest::ORGANIZATION_TYPES, true) ? null : $request->organization,
            ],
        ];
        $workOrder = ['sector' => WorkOrder::sectorForCategory($request->category)];

        if ($this->startsFromWorkOrders) {
            $workOrder += $client;
            $workOrder['details']['client'] = WorkOrderForm::clientDetails($existing);
        }

        $this->form->fill([
            ...($this->startsFromWorkOrders ? [] : $client),
            'consultancy_request_id' => $request->id,
            'title' => 'Consultancy proposal for '.($request->name ?: 'client'),
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'categories' => array_filter([$request->service_category_id]),
            'extra_discount' => 0,
            'tax_rate' => 0,
            'notes' => $request->requirement ? 'Request: '.$request->requirement : null,
            'create_work_order' => false,
            'work_order' => $workOrder,
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        if ($this->startsFromWorkOrders || ($data['create_work_order'] ?? false)) {
            abort_unless(WorkOrderResource::canCreate(), 403);
            $sector = $data['work_order']['sector'] ?? null;

            if (! is_string($sector) || ! array_key_exists($sector, WorkOrder::sectorOptions())) {
                throw ValidationException::withMessages([
                    'data.work_order.sector' => $this->startsFromWorkOrders ? 'Choose a work order category.' : 'Choose a work order sector.',
                ]);
            }

            $this->workOrderData = $data['work_order'];
        }

        if ($this->startsFromWorkOrders) {
            $data['delivery_timeline'] = Proposal::deliveryTimelineForDates($data['issue_date'], $data['valid_until'] ?? null);
            $this->workOrderData['details']['start_date'] = $data['issue_date'];
            $this->workOrderData['details']['end_date'] = $data['valid_until'] ?? null;
        }

        $client = $this->startsFromWorkOrders ? $data['work_order'] : $data;
        $mode = $client['client_mode'] ?? 'existing';
        unset($data['client_mode'], $data['new_client'], $data['create_work_order'], $data['work_order']);

        if ($mode === 'new') {
            $resolved = ClientResolver::resolve((array) ($client['new_client'] ?? []), auth()->id());
            ClientPortalAccess::inviteNewClient($resolved, $this->startsFromWorkOrders
                ? 'data.work_order.new_client.email'
                : 'data.new_client.email');

            $data['client_id'] = $resolved->id;
        } else {
            $data['client_id'] = $client['client_id'];
        }

        return Proposal::create($data + ['status' => 'draft', 'created_by' => auth()->id()]);
    }

    protected function afterCreate(): void
    {
        $this->record->recalculate();

        if ($this->workOrderData === null) {
            return;
        }

        $this->workOrder = WorkOrder::create(WorkOrderForm::linkedDraftAttributes($this->record, $this->workOrderData));
    }

    protected function getRedirectUrl(): string
    {
        if ($this->workOrder && WorkOrderResource::canEdit($this->workOrder)) {
            return WorkOrderResource::editUrl($this->workOrder);
        }

        return ProposalResource::getUrl('view', ['record' => $this->record]);
    }
}
