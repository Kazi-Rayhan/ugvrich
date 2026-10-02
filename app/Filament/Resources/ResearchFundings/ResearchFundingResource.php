<?php

namespace App\Filament\Resources\ResearchFundings;

use App\Filament\Resources\ResearchFundings\Pages\CreateResearchFunding;
use App\Filament\Resources\ResearchFundings\Pages\EditResearchFunding;
use App\Filament\Resources\ResearchFundings\Pages\ListResearchFundings;
use App\Models\ResearchFunding;
use App\Models\ResearchProposal;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Funding decisions on approved proposals.
 *
 * A proposal may carry several of these — part-funded from one source and
 * part from another, or declined once and revisited — so this is a list, not
 * a panel on the proposal. Approving a proposal does not create one: somebody
 * has to decide, and an unfavourable decision is recorded too.
 */
class ResearchFundingResource extends Resource
{
    protected static ?string $model = ResearchFunding::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 9;

    protected static ?string $modelLabel = 'Funding Decision';

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', ResearchFunding::PENDING)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('The proposal')
                ->schema([
                    Select::make('research_proposal_id')
                        ->label('Proposal')
                        ->relationship(
                            'proposal',
                            'title',
                            // Only approved proposals are funded.
                            fn ($query) => $query->where('status', ResearchProposal::APPROVED),
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Approved proposals only. A proposal may have more than one funding record.'),
                ]),

            Section::make('Where the money comes from')
                ->columns(2)
                ->description('The university\'s own money, or somebody else\'s. The rest of the form follows from this.')
                ->schema([
                    Radio::make('source_type')
                        ->label('Source')
                        ->options(ResearchFunding::sourceTypes())
                        ->default(ResearchFunding::INTERNAL)
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label('Kind of grant')
                        // The kinds on offer depend on the source, so this
                        // follows the radio above rather than listing all of them.
                        ->options(fn ($get) => ResearchFunding::types($get('source_type')))
                        ->native(false),

                    TextInput::make('source')
                        ->label('Programme or call')
                        ->maxLength(180)
                        ->placeholder('e.g. Research Grant Call 2026')
                        ->helperText('The scheme this money comes under, where it has a name.'),

                    Select::make('organization_type')
                        ->label('Kind of funder')
                        ->options(ResearchFunding::organizationTypes())
                        ->native(false)
                        ->visible(fn ($get) => $get('source_type') === ResearchFunding::EXTERNAL),

                    TextInput::make('organization')
                        ->label('Funding organisation')
                        ->maxLength(180)
                        // A list of suggestions, not a closed set: the same body
                        // should not end up typed five different ways.
                        ->datalist(ResearchFunding::organizations())
                        ->required(fn ($get) => $get('source_type') === ResearchFunding::EXTERNAL)
                        ->visible(fn ($get) => $get('source_type') === ResearchFunding::EXTERNAL),
                ]),

            Section::make('The decision')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(ResearchFunding::statuses())
                        ->default(ResearchFunding::PENDING)
                        ->required()
                        ->native(false)
                        ->columnSpanFull(),

                    TextInput::make('requested_amount')->numeric()->prefix(fn ($get) => $get('currency') ?: 'BDT'),
                    TextInput::make('approved_amount')->numeric()->prefix(fn ($get) => $get('currency') ?: 'BDT')
                        ->helperText('Leave empty where nothing has been awarded.'),

                    TextInput::make('currency')->default('BDT')->maxLength(8)->required(),

                    DatePicker::make('starts_on')->label('Funding starts'),
                    DatePicker::make('ends_on')->label('Funding ends'),

                    Textarea::make('decision')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('The researcher sees this.'),

                    Textarea::make('notes')
                        ->label('Internal notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Not shown to the researcher.'),

                    FileUpload::make('document')
                        ->label('Approval document')
                        ->disk('public')
                        ->directory('research-funding')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('proposal.title')
                    ->label('Proposal')
                    ->searchable()
                    ->wrap()
                    ->weight('semibold')
                    ->description(fn (ResearchFunding $record) => $record->proposal?->user?->name),

                TextColumn::make('source_type')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ResearchFunding::sourceTypes()[$state] ?? $state)
                    ->color(fn (?string $state) => $state === ResearchFunding::EXTERNAL ? 'warning' : 'gray')
                    // Internal money needs no funder named; external money does.
                    ->description(fn (ResearchFunding $record) => $record->isExternal() ? $record->funder() : null),

                TextColumn::make('type')
                    ->label('Grant')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? (ResearchFunding::types()[$state] ?? $state) : '—')
                    ->description(fn (ResearchFunding $record) => $record->source)
                    ->toggleable(),

                TextColumn::make('approved_amount')
                    ->label('Awarded')
                    ->formatStateUsing(fn (ResearchFunding $record) => $record->amount() ?? '—')
                    ->alignEnd(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ResearchFunding::statuses()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ResearchFunding::APPROVED => 'success',
                        ResearchFunding::PARTIAL => 'info',
                        ResearchFunding::UNFUNDED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('decided_at')->dateTime('j M Y')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResearchFunding::statuses()),
                SelectFilter::make('source_type')->label('Source')->options(ResearchFunding::sourceTypes()),
                SelectFilter::make('type')->label('Grant')->options(ResearchFunding::types()),
                SelectFilter::make('organization_type')
                    ->label('Kind of funder')
                    ->options(ResearchFunding::organizationTypes()),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchFundings::route('/'),
            'create' => CreateResearchFunding::route('/create'),
            'edit' => EditResearchFunding::route('/{record}/edit'),
        ];
    }
}
