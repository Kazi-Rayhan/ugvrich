<?php

namespace App\Filament\Resources\FinanceTransactions\Pages;

use App\Models\FinanceTransaction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListFinanceTransfers extends ListFinanceTransactions
{
    protected static ?string $title = 'Transfers';

    public function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query) => $query->where('kind', FinanceTransaction::TRANSFER));
    }

    protected function getHeaderActions(): array
    {
        return [$this->recordAction(FinanceTransaction::TRANSFER, 'Record transfer'), $this->exportAction()];
    }
}
