<?php

namespace App\Filament\Resources\ServiceCategories\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'Services in this area';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Languages')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('English')->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(150)
                                ->columnSpanFull()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                            Textarea::make('description')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                        Tab::make('বাংলা')->schema([
                            TextInput::make('name_bn')
                                ->label('Name')
                                ->maxLength(150)
                                ->helperText('খালি রাখলে ইংরেজি লেখাটিই দেখানো হবে · Leave empty to show the English text.'),

                            Textarea::make('description_bn')
                                ->label('Description')
                                ->rows(3)
                                ->helperText('খালি রাখলে ইংরেজি লেখাটিই দেখানো হবে · Leave empty to show the English text.'),
                        ]),
                    ]),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(150),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),

                TextInput::make('price')
                    ->label('Default price')
                    ->numeric()
                    ->minValue(0)
                    ->step('0.01')
                    ->prefix('BDT')
                    ->helperText('Used as the starting price on proposals.'),

                Toggle::make('is_active')
                    ->label('Visible on the site')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn ($record) => Str::limit($record->description, 90)),

                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
