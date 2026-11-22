<?php

namespace App\Filament\Resources\ResearcherUsers;

use App\Filament\Resources\ResearcherUsers\Pages\CreateResearcherUser;
use App\Filament\Resources\ResearcherUsers\Pages\EditResearcherUser;
use App\Filament\Resources\ResearcherUsers\Pages\ListResearcherUsers;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResearcherUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'researcher-users';

    protected static ?string $modelLabel = 'Researcher user';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', User::RESEARCHER);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAnyResearcher', User::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('createResearcher', User::class) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('updateResearcher', $record) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(150),
            TextInput::make('email')->email()->required()->maxLength(180)->unique(ignoreRecord: true),
            TextInput::make('password')->password()->revealable()->minLength(8)
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('name')->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('created_at')->label('Joined')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearcherUsers::route('/'),
            'create' => CreateResearcherUser::route('/create'),
            'edit' => EditResearcherUser::route('/{record}/edit'),
        ];
    }
}
