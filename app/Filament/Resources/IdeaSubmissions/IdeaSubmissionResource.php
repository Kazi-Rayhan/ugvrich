<?php

namespace App\Filament\Resources\IdeaSubmissions;

use App\Filament\Resources\IdeaSubmissions\Pages\EditIdeaSubmission;
use App\Filament\Resources\IdeaSubmissions\Pages\ListIdeaSubmissions;
use App\Filament\Resources\IdeaSubmissions\Pages\ViewIdeaSubmission;
use App\Filament\Resources\IdeaSubmissions\Schemas\IdeaSubmissionForm;
use App\Filament\Resources\IdeaSubmissions\Tables\IdeaSubmissionsTable;
use App\Models\IdeaSubmission;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IdeaSubmissionResource extends Resource
{
    protected static ?string $model = IdeaSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Idea Submission';

    public static function getNavigationBadge(): ?string
    {
        $new = static::getModel()::where('status', 'new')->count();

        return $new > 0 ? (string) $new : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return IdeaSubmissionForm::configure($schema);
    }

    /** The idea as it was sent, for reading. */
    public static function infolist(Schema $schema): Schema
    {
        $status = fn (?string $state) => match ($state) {
            'new' => 'warning',
            'in_review' => 'info',
            'accepted' => 'success',
            'declined' => 'danger',
            default => 'gray',
        };

        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    TextEntry::make('reference')->label('Reference')->weight('bold')->copyable(),
                    TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i A'),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (?string $state) => str($state)->replace('_', ' ')->title())
                        ->color($status),
                    TextEntry::make('stage')
                        ->label('Journey stage')
                        ->badge()
                        ->color('info')
                        ->formatStateUsing(fn (?string $state) => config('rich.startup_stages.'.$state, $state))
                        ->placeholder('—'),
                ]),

            Section::make('The idea')
                ->icon('heroicon-o-light-bulb')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->hiddenLabel()->size('lg')->weight('bold')->columnSpanFull(),
                    TextEntry::make('category')
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (?string $state) => config('rich.idea_categories.'.$state, $state))
                        ->placeholder('Not given'),
                    TextEntry::make('team_size')->label('Team size')->placeholder('—'),
                    TextEntry::make('problem')->label('The problem')->prose()->columnSpanFull()->placeholder('—'),
                    TextEntry::make('solution')->label('The solution')->prose()->columnSpanFull()->placeholder('—'),
                    TextEntry::make('beneficiaries')->label('Who benefits')->prose()->columnSpanFull()->placeholder('—'),
                    TextEntry::make('resources_needed')->label('Support needed')->prose()->columnSpanFull()->placeholder('—'),
                    TextEntry::make('document')
                        ->label('Attached file')
                        ->formatStateUsing(fn (?string $state) => $state ? basename($state) : null)
                        ->url(fn ($record) => $record->document ? Storage::disk('public')->url($record->document) : null, shouldOpenInNewTab: true)
                        ->icon('heroicon-o-paper-clip')
                        ->color('primary')
                        ->placeholder('No file attached')
                        ->columnSpanFull(),
                ]),

            Section::make('Submitted by')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')->weight('semibold'),
                    TextEntry::make('phone')->copyable()->icon('heroicon-m-phone')->placeholder('—'),
                    TextEntry::make('email')->copyable()->icon('heroicon-m-envelope')->placeholder('—'),
                    TextEntry::make('role')->formatStateUsing(fn (?string $state) => config('rich.idea_roles.'.$state, $state))->placeholder('—'),
                    TextEntry::make('department')->formatStateUsing(fn ($state, $record) => $record->department_name ?? $state)->placeholder('—'),
                    TextEntry::make('programme')->label('Programme / designation')->placeholder('—'),
                ]),

            Section::make('Handling')
                ->icon('heroicon-o-clipboard-document-check')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextEntry::make('reviewed_at')->label('Reviewed at')->dateTime('j M Y, g:i A')->placeholder('Not yet'),
                    TextEntry::make('admin_notes')->label('Internal notes')->placeholder('None')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return IdeaSubmissionsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        // Ideas only ever arrive through the public form.
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdeaSubmissions::route('/'),
            'view' => ViewIdeaSubmission::route('/{record}'),
            'edit' => EditIdeaSubmission::route('/{record}/edit'),
        ];
    }
}
