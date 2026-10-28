<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

/**
 * Progress, dates and staff can always change. The lines and figures can only
 * change while no payment is pending or confirmed; the form locks them, and
 * the check after saving makes sure nothing got past the lock.
 */
class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected ?string $totalBeforeSave = null;

    protected bool $wasLocked = false;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }

    protected function beforeSave(): void
    {
        $fresh = Order::findOrFail($this->record->id);
        $this->totalBeforeSave = (string) $fresh->total;
        $this->wasLocked = $fresh->linesAreLocked();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->proposal_id !== null) {
            unset($data['client_id']);
        }

        if ($this->wasLocked) {
            unset($data['extra_discount'], $data['tax_rate']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculate();

        // Rolls the whole save back (the page runs in a transaction).
        if ($this->wasLocked && (string) $this->record->total !== $this->totalBeforeSave) {
            throw ValidationException::withMessages([
                'data.items' => 'Payments have been recorded against this order, so its figures cannot change.',
            ]);
        }
    }

    protected function getRedirectUrl(): ?string
    {
        return OrderResource::getUrl('view', ['record' => $this->record]);
    }
}
