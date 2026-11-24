<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Support\Sales\ClientPortalAccess;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $password = $data['portal_password'] ?? null;
        unset($data['portal_password']);

        if (filled($password)) {
            $record = Client::whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            parent::handleRecordUpdate($record, $data);
            ClientPortalAccess::setPassword($record, $password, auth()->user());
            $this->data['portal_password'] = null;
            $this->record->refresh();

            return $record;
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            // A client with proposals or orders stays: those records point at it.
            DeleteAction::make()
                ->visible(fn (Client $record) => ! $record->proposals()->exists() && ! $record->orders()->exists()),
        ];
    }
}
