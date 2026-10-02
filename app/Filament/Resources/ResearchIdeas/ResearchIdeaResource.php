<?php

namespace App\Filament\Resources\ResearchIdeas;

use App\Filament\Resources\ResearchIdeas\Pages\ListResearchIdeas;
use App\Filament\Resources\ResearchIdeas\Pages\ViewResearchIdea;
use App\Filament\Support\ResearchReviewActions;
use App\Models\ResearchIdea;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Research ideas sent in through the Researcher Portal.
 *
 * Read-only on purpose: an idea is the researcher's own words and staff do not
 * edit it. What staff do is assign a reviewer, mark it as being looked at, and
 * record a decision — each of which is an action, and each of which leaves a
 * row in the review history rather than overwriting anything.
 */
class ResearchIdeaResource extends Resource
{
    protected static ?string $model = ResearchIdea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Research Idea';

    public static function getNavigationBadge(): ?string
    {
        $waiting = static::getModel()::whereIn('status', [ResearchIdea::SUBMITTED, ResearchIdea::UNDER_REVIEW])->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Ideas arrive from the portal; nobody writes one in here. */
    public static function canCreate(): bool
    {
        return false;
    }

    /** Drafts belong to the researcher alone until they choose to send them. */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', ResearchIdea::DRAFT);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('The idea')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->columnSpanFull()->weight('bold'),
                    TextEntry::make('user.name')->label('Researcher'),
                    TextEntry::make('user.email')->label('Email')->copyable(),
                    TextEntry::make('department'),
                    TextEntry::make('research_field'),
                    TextEntry::make('research_area')->placeholder('—'),
                    TextEntry::make('collaboration_requirement')->label('Collaboration')->placeholder('—'),
                    TextEntry::make('keywords')->badge()->placeholder('—'),
                    TextEntry::make('sdgs')->label('SDGs')->badge()->placeholder('—'),
                ]),

            Section::make('What was written')
                ->schema([
                    TextEntry::make('problem')->label('Research problem')->prose(),
                    TextEntry::make('description')->label('Short description')->prose(),
                    TextEntry::make('motivation')->prose()->placeholder('—'),
                    TextEntry::make('expected_contribution')->prose()->placeholder('—'),
                    TextEntry::make('proposed_team')->label('Proposed team')->prose()->placeholder('—'),
                ]),

            Section::make('Where it stands')
                ->columns(3)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => ResearchIdea::statuses()[$state] ?? $state),
                    TextEntry::make('assignedReviewer.name')->label('Assigned to')->placeholder('Nobody yet'),
                    TextEntry::make('submitted_at')->dateTime('j M Y, g:i a')->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('j M Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->weight('semibold')
                    ->description(fn (ResearchIdea $record) => $record->user?->name),

                TextColumn::make('department')->badge()->color('primary')->sortable()
                    ->description(fn (ResearchIdea $record) => $record->research_field),

                TextColumn::make('assignedReviewer.name')->label('Reviewer')->placeholder('—')->toggleable(),

                TextColumn::make('reviews_count')->counts('reviews')->label('Reviews')->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ResearchIdea::statuses()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ResearchIdea::SUBMITTED => 'warning',
                        ResearchIdea::UNDER_REVIEW => 'info',
                        ResearchIdea::REVISION => 'warning',
                        ResearchIdea::APPROVED, ResearchIdea::PROPOSAL => 'success',
                        ResearchIdea::REJECTED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResearchIdea::statuses()),
                SelectFilter::make('department')
                    ->options(fn () => ResearchIdea::query()->distinct()->pluck('department', 'department')->filter()->all()),
                SelectFilter::make('assigned_reviewer_id')
                    ->label('Reviewer')
                    ->options(fn () => \App\Support\ResearchWorkflow::reviewers()),
            ])
            ->recordActions(ResearchReviewActions::all());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchIdeas::route('/'),
            'view' => ViewResearchIdea::route('/{record}'),
        ];
    }
}
