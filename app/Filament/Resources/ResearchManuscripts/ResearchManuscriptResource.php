<?php

namespace App\Filament\Resources\ResearchManuscripts;

use App\Filament\Resources\ResearchManuscripts\Pages\ListResearchManuscripts;
use App\Filament\Resources\ResearchManuscripts\Pages\ViewResearchManuscript;
use App\Filament\Support\ResearchReviewActions;
use App\Models\ResearchManuscript;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Manuscripts submitted for internal review.
 *
 * Read-only, like ideas and proposals: the text is the researcher's. Staff
 * assign a reviewer, mark it under review, and record decisions — each of
 * which leaves a row in the shared review history.
 *
 * The two actions that are this screen's alone are publication: marking a
 * manuscript published, and deciding whether it belongs in the public
 * repository. Those are deliberately separate, because being published does
 * not by itself make something public here.
 */
class ResearchManuscriptResource extends Resource
{
    protected static ?string $model = ResearchManuscript::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Manuscript';

    public static function getNavigationBadge(): ?string
    {
        $waiting = static::getModel()::whereIn('status', [
            ResearchManuscript::SUBMITTED,
            ResearchManuscript::INTERNAL_REVIEW,
        ])->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** A draft is the researcher's alone until they submit it. */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', ResearchManuscript::DRAFT);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('The paper')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->columnSpanFull()->weight('bold'),
                    TextEntry::make('user.name')->label('Researcher'),
                    TextEntry::make('user.email')->label('Email')->copyable(),
                    TextEntry::make('project.title')->label('Project')->placeholder('Not linked'),
                    TextEntry::make('manuscript_type')->placeholder('—'),
                    TextEntry::make('keywords')->badge()->placeholder('—'),
                    TextEntry::make('abstract')->columnSpanFull()->prose(),
                ]),

            Section::make('Authors and venue')
                ->columns(2)
                ->schema([
                    TextEntry::make('primary_author')->placeholder('—'),
                    TextEntry::make('corresponding_author')->placeholder('—'),
                    TextEntry::make('co_authors')->columnSpanFull()->placeholder('—'),
                    TextEntry::make('target_journal')->placeholder('—'),
                    TextEntry::make('publisher')->placeholder('—'),
                    TextEntry::make('quartile')->placeholder('—'),
                    TextEntry::make('impact_factor')->placeholder('—'),
                ]),

            Section::make('Declarations')
                ->schema([
                    TextEntry::make('funding_source')->placeholder('—'),
                    TextEntry::make('ethics_approval')->placeholder('—'),
                    TextEntry::make('conflict_of_interest')->prose()->placeholder('—'),
                    TextEntry::make('ai_use_declaration')->label('AI use declaration')->prose()->placeholder('—'),
                ]),

            Section::make('Where it stands')
                ->columns(3)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => ResearchManuscript::statuses()[$state] ?? $state),
                    TextEntry::make('assignedReviewer.name')->label('Assigned to')->placeholder('Nobody yet'),
                    TextEntry::make('submitted_at')->dateTime('j M Y')->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('submitted_at')->label('Submitted')->dateTime('j M Y')->sortable()->placeholder('—'),

                TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->weight('semibold')
                    ->description(fn (ResearchManuscript $record) => $record->user?->name),

                TextColumn::make('target_journal')->label('Journal')->toggleable()->placeholder('—'),

                TextColumn::make('assignedReviewer.name')->label('Reviewer')->placeholder('—')->toggleable(),

                TextColumn::make('reviews_count')->counts('reviews')->label('Reviews')->alignCenter(),

                IconColumn::make('is_public')->label('In repository')->boolean()->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ResearchManuscript::statuses()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ResearchManuscript::SUBMITTED, ResearchManuscript::REVISION => 'warning',
                        ResearchManuscript::INTERNAL_REVIEW, ResearchManuscript::JOURNAL_REVIEW => 'info',
                        ResearchManuscript::APPROVED, ResearchManuscript::ACCEPTED, ResearchManuscript::PUBLISHED => 'success',
                        ResearchManuscript::REJECTED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResearchManuscript::statuses()),
                TernaryFilter::make('is_public')->label('In the public repository'),
            ])
            ->recordActions(ResearchReviewActions::forManuscripts());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchManuscripts::route('/'),
            'view' => ViewResearchManuscript::route('/{record}'),
        ];
    }
}
