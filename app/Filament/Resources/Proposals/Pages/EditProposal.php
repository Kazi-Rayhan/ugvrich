<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\WorkOrderForm;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\Proposal;
use App\Models\WorkOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/** Editing a proposal and its linked work order uses one transaction. */
class EditProposal extends EditRecord
{
    protected static string $resource = ProposalResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    #[Locked]
    public ?int $workOrderId = null;

    protected ?array $workOrderInput = null;

    protected bool $clientChanged = false;

    public function getLinkedWorkOrder(): ?WorkOrder
    {
        return once(fn () => $this->workOrderId
            ? WorkOrder::where('proposal_id', $this->record->getKey())->find($this->workOrderId)
            : null);
    }

    public function canManageWorkOrder(): bool
    {
        $workOrder = $this->getLinkedWorkOrder();

        return $workOrder ? WorkOrderResource::canEdit($workOrder) : WorkOrderResource::canCreate();
    }

    protected function getHeaderActions(): array
    {
        $workOrder = $this->getLinkedWorkOrder();

        return [
            ...($workOrder ? [
                WorkOrderResource::acceptAction()->record($workOrder),
                WorkOrderResource::confirmAction()->record($workOrder),
            ] : []),
            Action::make('printWorkOrder')
                ->label('Work order PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn () => $workOrder && WorkOrder::sector($workOrder->sector) !== [] && WorkOrderResource::canView($workOrder))
                ->url(fn () => ($workOrder = $this->getLinkedWorkOrder()) ? route('sales.work-orders.print', $workOrder) : null)
                ->openUrlInNewTab(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /** Someone may have accepted it while this form was open. */
    protected function beforeSave(): void
    {
        if (! Proposal::lockForUpdate()->find($this->record->id)?->isEditable()) {
            Notification::make()->title('This proposal has been accepted or closed and can no longer be edited.')->danger()->send();
            $this->halt();
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $workOrders = WorkOrder::where('proposal_id', $this->record->getKey());
        $selectedId = request()->integer('work_order');
        $workOrder = $selectedId ? $workOrders->findOrFail($selectedId) : $workOrders->latest('id')->first();

        if ($selectedId) {
            abort_unless(WorkOrderResource::canEdit($workOrder), 403);
        }

        $this->workOrderId = $workOrder?->id;

        if ($workOrder && WorkOrderResource::canEdit($workOrder)) {
            $data['work_order'] = $workOrder->only(['sector', 'details']);
        } elseif (! $workOrder && WorkOrderResource::canCreate()) {
            $data['work_order']['details']['client'] = WorkOrderForm::clientDetails($this->record->client);
        }

        $data['work_order']['client_id'] = $data['client_id'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->workOrderInput = $this->canManageWorkOrder()
            && ($this->workOrderId !== null || filled($data['work_order']['sector'] ?? null))
            ? $data['work_order']
            : null;
        $data['client_id'] = $data['work_order']['client_id'];
        $this->clientChanged = (int) $data['client_id'] !== (int) $this->record->client_id;
        unset($data['client_mode'], $data['new_client'], $data['work_order']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculate();

        if ($this->workOrderInput === null) {
            return;
        }

        $input = $this->workOrderInput;
        $workOrders = WorkOrder::where('proposal_id', $this->record->getKey());

        if ($this->workOrderId !== null) {
            $workOrder = $workOrders->lockForUpdate()->find($this->workOrderId);
            abort_unless($workOrder && WorkOrderResource::canEdit($workOrder), 403);
            $details = array_replace($workOrder->details ?? [], $input['details'] ?? []);

            if (blank(data_get($details, 'client.name'))) {
                $client = $this->record->client;
                data_set($details, 'client.name', $client?->organization ?: ($client?->name ?: $workOrder->client_name));
            }

            $workOrder->update([
                'sector' => $input['sector'] ?? null,
                ...($this->clientChanged ? ['client_id' => $this->record->client_id] : []),
                'details' => $details,
            ]);

            return;
        }

        abort_unless(WorkOrderResource::canCreate(), 403);

        if ($workOrders->exists()) {
            throw ValidationException::withMessages(['data.work_order.sector' => 'A work order has already been linked. Reload the proposal to edit it.']);
        }

        $workOrder = WorkOrder::create(WorkOrderForm::linkedDraftAttributes($this->record, $input));
        $this->workOrderId = $workOrder->id;
    }

    protected function getRedirectUrl(): ?string
    {
        return ProposalResource::getUrl('view', ['record' => $this->record]);
    }
}
