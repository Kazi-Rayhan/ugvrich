<?php

namespace App\Filament\Resources\FundingOpportunities;

use App\Filament\Resources\FundingOpportunities\Pages\CreateFundingOpportunity;
use App\Filament\Resources\FundingOpportunities\Pages\EditFundingOpportunity;
use App\Filament\Resources\FundingOpportunities\Pages\ListFundingOpportunities;
use App\Models\FundingOpportunity;
use App\Support\ResearchTaxonomy;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Funding opportunities researchers can apply for.
 *
 * Nothing is seeded. Grants, funders, deadlines and amounts are facts about the
 * outside world, and inventing them would put false information in front of
 * researchers — so the list stays empty until somebody who knows adds a real one.
 */
class FundingOpportunityResource extends Resource
{
    protected static ?string $model = FundingOpportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Funding Opportunity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('The opportunity')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                    TextInput::make('slug')->required()->maxLength(200)->unique(ignoreRecord: true),

                    Select::make('category')
                        ->options(FundingOpportunity::categories())
                        ->required()
                        ->native(false),

                    TextInput::make('organization')
                        ->label('Funder / organisation')
                        ->maxLength(180),

                    TextInput::make('amount')
                        ->maxLength(120)
                        ->helperText('As the funder states it, e.g. "up to BDT 200,000".'),

                    DatePicker::make('deadline')
                        ->helperText('Leave empty if the call is open-ended.'),

                    Select::make('department')
                        ->options(fn () => collect(ResearchTaxonomy::departments())->mapWithKeys(fn ($d) => [$d => $d])->all())
                        ->native(false)
                        ->searchable()
                        ->helperText('Optional. Leave empty if open to every department.'),

                    TextInput::make('research_area')->maxLength(180),
                ]),

            Section::make('Detail')
                ->schema([
                    Textarea::make('description')->rows(5),
                    Textarea::make('eligibility')->rows(4),
                    Textarea::make('how_to_apply')->label('How to apply')->rows(4),
                    TextInput::make('external_url')->label('Funder link')->url()->maxLength(255),
                    FileUpload::make('document')->disk('public')->directory('funding-opportunities'),
                ]),

            Section::make('Display')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')->label('Visible to researchers')->default(true),
                    TextInput::make('sort_order')->numeric()->default(0)->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('deadline')
            ->columns([
                TextColumn::make('title')->searchable()->wrap()->weight('semibold')
                    ->description(fn (FundingOpportunity $record) => $record->organization),

                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => FundingOpportunity::categories()[$state] ?? $state),

                TextColumn::make('deadline')
                    ->date('j M Y')
                    ->sortable()
                    ->placeholder('Open-ended')
                    ->color(fn (FundingOpportunity $record) => $record->hasClosed() ? 'danger' : 'gray'),

                TextColumn::make('amount')->toggleable()->placeholder('—'),
                TextColumn::make('department')->badge()->color('primary')->toggleable()->placeholder('Any'),

                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')->options(FundingOpportunity::categories()),
                TernaryFilter::make('is_active')->label('Visible to researchers'),
                Filter::make('open')
                    ->label('Still open')
                    ->query(fn ($query) => $query->open()),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFundingOpportunities::route('/'),
            'create' => CreateFundingOpportunity::route('/create'),
            'edit' => EditFundingOpportunity::route('/{record}/edit'),
        ];
    }
}
