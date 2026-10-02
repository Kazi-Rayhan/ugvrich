<?php

namespace App\Filament\Resources\ResearchProposals;

use App\Filament\Resources\ResearchProposals\Pages\EditResearchProposal;
use App\Filament\Resources\ResearchProposals\Pages\ListResearchProposals;
use App\Filament\Support\ResearchReviewActions;
use App\Models\ResearchProposal;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Research proposals sent in through the Research page.
 *
 * Read-mostly: the submission itself is what the researcher wrote and is shown
 * as it arrived. Only the triage fields — status, notes, when it was read — can
 * be edited here.
 */
class ResearchProposalResource extends Resource
{
    protected static ?string $model = ResearchProposal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Research Proposal';

    public static function getNavigationBadge(): ?string
    {
        $waiting = static::getModel()::whereIn('status', [
            ResearchProposal::SUBMITTED,
            ResearchProposal::UNDER_REVIEW,
        ])->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        // These arrive from the public site or the portal; nobody writes one here.
        return false;
    }

    /** A draft belongs to the researcher alone until they send it. */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', ResearchProposal::DRAFT);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Triage')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(ResearchProposal::statuses())
                        ->default('new')
                        ->native(false)
                        ->required(),

                    DateTimePicker::make('reviewed_at')
                        ->label('Read on')
                        ->seconds(false),

                    Textarea::make('admin_notes')
                        ->label('Internal notes')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Not shown to the person who sent it.'),
                ]),

            Section::make('What was sent')
                ->description('As the researcher wrote it.')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('phone')
                        ->label('Phone')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('department')
                        ->label('Department')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('research_field')
                        ->label('Research field')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('research_area')
                        ->label('Research area')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('title')
                        ->label('Proposal title')
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('background')
                        ->label('Research background / problem statement')
                        ->rows(5)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('objectives')
                        ->label('Objectives')
                        ->rows(4)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('research_gap')
                        ->label('Research gap')
                        ->rows(4)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('research_questions')
                        ->label('Research questions / hypotheses')
                        ->rows(4)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('methodology')
                        ->label('Methodology')
                        ->rows(4)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('expected_outcome')
                        ->label('Expected outcomes')
                        ->rows(4)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('expected_impact')
                        ->label('Potential impact')
                        ->rows(4)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('timeline')
                        ->label('Timeline')
                        ->rows(3)
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                ]),

            Section::make('Researcher and team')
                ->columns(2)
                ->schema([
                    TextInput::make('designation')->disabled()->dehydrated(false),
                    TextInput::make('institution')->disabled()->dehydrated(false),
                    TextInput::make('researcher_department')->disabled()->dehydrated(false),
                    TextInput::make('researcher_type')
                        ->formatStateUsing(fn (?string $state) => $state ? (ResearchProposal::researcherTypes()[$state] ?? $state) : '—')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('researcher_type_other')->disabled()->dehydrated(false),
                    TextInput::make('research_type')
                        ->formatStateUsing(fn (?string $state) => $state ? (ResearchProposal::researchTypes()[$state] ?? $state) : '—')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('principal_investigator')->label('Principal investigator')->disabled()->dehydrated(false),
                    Textarea::make('co_researchers')
                        ->formatStateUsing(fn (?array $state) => collect($state ?? [])
                            ->map(fn (array $researcher) => implode(' · ', array_filter([
                                $researcher['name'] ?? null,
                                $researcher['designation'] ?? null,
                                $researcher['department'] ?? null,
                            ])))
                            ->implode("\n"))
                        ->rows(4)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('external_collaborator')->disabled()->dehydrated(false),
                    TextInput::make('external_department')->disabled()->dehydrated(false),
                    TextInput::make('external_institution')->disabled()->dehydrated(false),
                ]),

            Section::make('Funding and ethics')
                ->columns(2)
                ->schema([
                    TextInput::make('funding_required')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('budget')->disabled()->dehydrated(false),
                    Textarea::make('budget_breakdown')->rows(3)->disabled()->dehydrated(false),
                    TextInput::make('funding_source')->disabled()->dehydrated(false),
                    TextInput::make('external_funding_applied')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('sdgs')
                        ->formatStateUsing(fn (?array $state) => implode(', ', $state ?? []))
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('innovation_novelty')->rows(3)->disabled()->dehydrated(false),
                    TextInput::make('human_participants')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('sensitive_data')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('ethical_approval_required')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('informed_consent_required')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('ai_used')
                        ->formatStateUsing(fn ($state) => is_null($state) ? '—' : ($state ? 'Yes' : 'No'))
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('j M Y, g:i a')
                    ->sortable()
                    ->description(fn ($record) => $record->reference()),

                TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->name.' · '.$record->email),

                TextColumn::make('department')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->description(fn ($record) => $record->research_field),

                TextColumn::make('research_area')
                    ->label('Area')
                    ->wrap()
                    ->toggleable()
                    ->color('gray'),

                // Portal proposals have an owner; public-form ones do not.
                TextColumn::make('user.name')
                    ->label('Researcher')
                    ->placeholder('Public form')
                    ->toggleable()
                    ->description(fn (ResearchProposal $record) => $record->idea?->title),

                TextColumn::make('assignedReviewer.name')
                    ->label('Reviewer')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviews_count')
                    ->counts('reviews')
                    ->label('Reviews')
                    ->alignCenter()
                    ->toggleable(),


                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ResearchProposal::statuses()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ResearchProposal::SUBMITTED => 'warning',
                        ResearchProposal::UNDER_REVIEW => 'info',
                        ResearchProposal::REVISION => 'warning',
                        ResearchProposal::APPROVED => 'success',
                        ResearchProposal::REJECTED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResearchProposal::statuses()),
                SelectFilter::make('department')
                    ->options(fn () => ResearchProposal::query()->distinct()->pluck('department', 'department')->filter()->all()),

            ])
            ->recordActions([
                ...ResearchReviewActions::forProposals(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchProposals::route('/'),
            'edit' => EditResearchProposal::route('/{record}/edit'),
        ];
    }
}
