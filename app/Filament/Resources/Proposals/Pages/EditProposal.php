<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Models\Proposal;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/** Editing keeps the same client; the figures are recomputed after every save. */
class EditProposal extends EditRecord
{
    protected static string $resource = ProposalResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }

    /** Someone may have accepted it while this form was open. */
    protected function beforeSave(): void
    {
        if (! Proposal::find($this->record->id)?->isEditable()) {
            Notification::make()->title('This proposal has been accepted or closed and can no longer be edited.')->danger()->send();
            $this->halt();
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['client_mode'], $data['new_client']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculate();
    }

    protected function getRedirectUrl(): ?string
    {
        return ProposalResource::getUrl('view', ['record' => $this->record]);
    }
}
