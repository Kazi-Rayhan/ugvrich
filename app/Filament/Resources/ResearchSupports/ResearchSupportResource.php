<?php

namespace App\Filament\Resources\ResearchSupports;

use App\Filament\Resources\ResearchSupports\Pages\EditResearchSupport;
use App\Filament\Resources\ResearchSupports\Pages\ListResearchSupports;
use App\Models\ResearchSupport;
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
 * Requests sent to the research support desk from the Research page.
 *
 * Read-mostly: the submission itself is what the researcher wrote and is shown
 * as it arrived. Only the triage fields — status, notes, when it was read — can
 * be edited here.
 */
class ResearchSupportResource extends Resource
{
    protected static ?string $model = ResearchSupport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|\UnitEnum|null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Research Support Request';

    public static function getNavigationBadge(): ?string
    {
        $new = static::getModel()::where('status', 'new')->count();

        return $new > 0 ? (string) $new : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        // These arrive from the public site; nobody writes one in here.
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Triage')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(ResearchSupport::statuses())
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
                    TextInput::make('role')
                        ->label('Role')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('department')
                        ->label('Department')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('stage')
                        ->label('Stage')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('needed_by')
                        ->label('Needed by')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('title')
                        ->label('Research title')
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('details')
                        ->label('What they need')
                        ->rows(6)
                        ->columnSpanFull()
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
                    ->toggleable(),

                TextColumn::make('support_types')
                    ->label('Asked for')
                    ->badge()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),


                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ResearchSupport::statuses()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'in_progress' => 'info',
                        'answered' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResearchSupport::statuses()),
                SelectFilter::make('department')
                    ->options(fn () => ResearchSupport::query()->distinct()->pluck('department', 'department')->filter()->all()),

            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchSupports::route('/'),
            'edit' => EditResearchSupport::route('/{record}/edit'),
        ];
    }
}
