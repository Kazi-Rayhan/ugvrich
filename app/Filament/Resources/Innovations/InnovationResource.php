<?php

namespace App\Filament\Resources\Innovations;

use App\Filament\Resources\Innovations\Pages\CreateInnovation;
use App\Filament\Resources\Innovations\Pages\EditInnovation;
use App\Filament\Resources\Innovations\Pages\ListInnovations;
use App\Filament\Support\Bilingual;
use App\Models\Innovation;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * The innovations on the public /innovation page.
 *
 * Two phases share one form. A current innovation is a short card — who leads
 * it and where it stands — while a proposed one is the document's full
 * write-up, so those fields only appear once the phase is set to proposed.
 */
class InnovationResource extends Resource
{
    protected static ?string $model = Innovation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Innovation';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Bilingual::tabs(Innovation::class, [
                Section::make('Innovation')
                    ->columns(2)
                    ->schema([
                        Select::make('innovation_area_id')
                            ->label('Innovation area')
                            ->relationship('area', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('The area it is listed under, as a service sits under its category.')
                            ->columnSpanFull(),

                        Select::make('phase')
                            ->options(Innovation::phases())
                            ->default(Innovation::CURRENT)
                            ->required()
                            ->live()
                            ->native(false)
                            ->helperText('Which section of the Innovation page this appears in.'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(150)
                            ->helperText('The English name. The Bangla tab holds the Bangla one, and each is shown in brackets beside the other.'),

                        TextInput::make('tagline')
                            ->maxLength(150)
                            ->helperText('The short line on the cover, e.g. "Clean Ride, Sun-Powered".'),

                        TextInput::make('subtitle')
                            ->maxLength(150)
                            ->visible(fn (Get $get) => $get('phase') === Innovation::PROPOSED)
                            ->helperText('Optional, e.g. "Mobile Solar Power Station".'),
                    ]),

                Section::make('Where it stands')
                    ->visible(fn (Get $get) => $get('phase') === Innovation::CURRENT)
                    ->schema([
                        TextInput::make('lead')
                            ->label('Lead & support departments')
                            ->maxLength(200)
                            ->helperText('e.g. "Mechanical & EEE (Lead), All Depts."'),

                        Textarea::make('highlights')
                            ->label('Key highlights')
                            ->rows(5)
                            ->helperText('What it does today. Write what comes next after "Next:" and the page sets it apart in its own panel.'),
                    ]),

                Section::make('The proposal')
                    ->visible(fn (Get $get) => $get('phase') === Innovation::PROPOSED)
                    ->schema([
                        Textarea::make('concept')->label('Concept')->rows(4),
                        Textarea::make('how')->label('How it works')->rows(5),
                        Textarea::make('why')->label('Why it matters')->rows(5),
                        Textarea::make('departments')->label('Departments involved')->rows(3),
                        TextInput::make('sdg')
                            ->label('SDGs')
                            ->maxLength(200)
                            ->helperText('e.g. "SDG 7 (Clean Energy), SDG 11 (Sustainable Transport)".'),
                    ]),

                Section::make('Presentation')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label('Cover photograph')
                            ->image()
                            ->disk('public')
                            ->directory('innovation')
                            ->imageEditor()
                            ->columnSpanFull()
                            ->helperText('Optional. Without one the page draws its own blueprint artwork from the name.'),

                        TextInput::make('number')
                            ->label('Document number')
                            ->maxLength(20)
                            ->helperText('Optional, from the proposal, e.g. "5.1.".'),

                        TextInput::make('sort_order')->numeric()->default(0)->required(),

                        Toggle::make('is_active')
                            ->label('Shown on the site')
                            ->default(true)
                            ->helperText('Hiding every innovation in a phase puts the original proposal text back.'),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->defaultGroup('phase')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->height(44)
                    ->width(66)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn (Innovation $record) => $record->getRawOriginal('name_bn')),

                TextColumn::make('tagline')
                    ->toggleable()
                    ->wrap()
                    ->limit(60)
                    ->color('gray'),

                TextColumn::make('area.name')
                    ->label('Innovation area')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Not set')
                    ->wrap(),

                TextColumn::make('phase')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === Innovation::CURRENT ? 'Current' : 'Proposed')
                    ->color(fn (string $state) => $state === Innovation::CURRENT ? 'success' : 'warning'),

                IconColumn::make('is_active')->label('Shown')->boolean(),
            ])
            ->filters([
                SelectFilter::make('innovation_area_id')
                    ->label('Innovation area')
                    ->relationship('area', 'name'),
                SelectFilter::make('phase')->options(Innovation::phases()),
                TernaryFilter::make('is_active')->label('Shown on the site'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInnovations::route('/'),
            'create' => CreateInnovation::route('/create'),
            'edit' => EditInnovation::route('/{record}/edit'),
        ];
    }
}
