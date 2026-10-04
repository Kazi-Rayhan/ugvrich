<?php

namespace App\Filament\Resources\Stats\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Stat;
use App\Filament\Resources\ServiceCategories\Schemas\ServiceCategoryForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Bilingual::tabs(Stat::class, [
                    Section::make('KPI')
                        ->description('The KPI cards on the home page dashboard, also used in the hero, About and Research pages. The number animates on scroll.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('label')->required()->maxLength(150)->columnSpanFull(),

                            TextInput::make('value')
                                ->required()
                                ->maxLength(20)
                                ->helperText('Digits only, e.g. 120'),

                            TextInput::make('suffix')
                                ->maxLength(10)
                                ->helperText('Shown after the number, e.g. + or %'),

                            Select::make('icon')
                                ->options(ServiceCategoryForm::ICONS)
                                ->native(false)
                                ->searchable(),

                            TextInput::make('sort_order')->numeric()->default(0)->required(),

                            Toggle::make('is_active')->label('Visible on the site')->default(true),

                            Toggle::make('show_on_home')
                                ->label('Show on the home page')
                                ->helperText('The home page dashboard keeps to a few headline figures. Off still shows it elsewhere on the site.')
                                ->default(true),
                        ]),
                ]),
            ]);
    }
}
