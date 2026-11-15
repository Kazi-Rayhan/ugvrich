<?php

namespace App\Filament\Resources\Experts\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Expert;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ExpertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Bilingual::tabs(Expert::class, [
                    Section::make('Identity')
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(150)
                                ->placeholder('Dr. Jane Doe')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(150)
                                ->unique(ignoreRecord: true),

                            Select::make('type')
                                ->label('Type')
                                ->options(['expert' => 'Expert', 'administration' => 'Administration'])
                                ->default('expert')
                                ->required()
                                ->in(['expert', 'administration']),

                            TextInput::make('designation')
                                ->maxLength(150)
                                ->placeholder('Professor'),

                            TextInput::make('department')
                                ->maxLength(150)
                                ->placeholder('Department of Civil Engineering'),

                            FileUpload::make('photo')
                                ->image()
                                ->avatar()
                                ->disk('public')
                                ->directory('experts')
                                ->imageEditor(),

                            Select::make('service_category_id')
                                ->label('Primary area of consultancy')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),
                        ]),

                    Section::make('Expertise')
                        ->schema([
                            TagsInput::make('expertise')
                                ->placeholder('Add an area and press Enter')
                                ->helperText('Shown as tags on the profile and used by directory search.')
                                ->columnSpanFull(),

                            Textarea::make('research_interests')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('bio')
                                ->label('Profile')
                                ->rows(6)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Contact & visibility')
                        ->columns(2)
                        ->schema([
                            TextInput::make('email')->email()->maxLength(180),
                            TextInput::make('phone')->tel()->maxLength(40),
                            TextInput::make('linkedin')->url()->maxLength(255),
                            TextInput::make('scholar')->label('Google Scholar')->url()->maxLength(255),
                            TextInput::make('sort_order')->numeric()->default(0)->required(),
                            Toggle::make('is_featured')->label('Feature on the homepage'),
                            Toggle::make('is_active')->label('Visible on the site')->default(true),
                        ]),
                ]),
            ]);
    }
}
