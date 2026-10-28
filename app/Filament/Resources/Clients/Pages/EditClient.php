<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // A client with proposals or orders stays: those records point at it.
            DeleteAction::make()
                ->visible(fn (Client $record) => ! $record->proposals()->exists() && ! $record->orders()->exists()),
        ];
    }
}
