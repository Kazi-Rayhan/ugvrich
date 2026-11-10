<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderForm;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Resources\Pages\CreateRecord;

/** A new work order; ?request= or ?proposal= start it from what is already known. */
class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected static ?string $title = 'New work order (সেবা কার্যাদেশ)';

    public function mount(): void
    {
        parent::mount();

        $this->form->fill(WorkOrderForm::prefill(
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
        return WorkOrderResource::getUrl('edit', ['record' => $this->record]);
    }
}
