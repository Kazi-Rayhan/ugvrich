<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\WorkOrderForm;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Resources\Pages\CreateRecord;

/** A new work order; ?request= or ?proposal= start it from what is already known. */
class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected static ?string $title = 'New work order';

    public function mount(): void
    {
        $this->authorizeAccess();

        if (! request()->boolean('draft') && ! request()->integer('proposal') && ProposalResource::canCreate()) {
            $this->redirect(ProposalResource::getUrl('create', array_filter([
                'work_order' => 1,
                'request' => request()->integer('request'),
            ])));

            return;
        }

        parent::mount();

        $this->form->fill(request()->boolean('draft') ? [] : WorkOrderForm::prefill(
            request()->integer('request') ?: null,
            request()->integer('proposal') ?: null,
        ));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['created_by' => auth()->id()];
    }

    protected function getRedirectUrl(): string
    {
        return WorkOrderResource::editUrl($this->record);
    }
}
