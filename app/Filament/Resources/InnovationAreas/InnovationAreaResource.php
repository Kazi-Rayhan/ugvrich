<?php

namespace App\Filament\Resources\InnovationAreas;

use App\Filament\Support\Bilingual;
use App\Filament\Resources\InnovationAreas\Pages\CreateInnovationArea;
use App\Filament\Resources\InnovationAreas\Pages\EditInnovationArea;
use App\Filament\Resources\InnovationAreas\Pages\ListInnovationAreas;
use App\Filament\Resources\ServiceCategories\Schemas\ServiceCategoryForm;
use App\Models\InnovationArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InnovationAreaResource extends Resource
{
    protected static ?string $model = InnovationArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Innovation';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Innovation area';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Bilingual::tabs(InnovationArea::class, [
                Section::make('Innovation Wing area')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(150)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')->required()->maxLength(150)->unique(ignoreRecord: true),

                        Select::make('department')
                            ->options(config('rich.departments'))
                            ->native(false)
                            ->searchable(),

                        Select::make('icon')
                            ->options(ServiceCategoryForm::ICONS)
                            ->native(false)
                            ->searchable(),

                        Textarea::make('description')->rows(3)->columnSpanFull(),

                        TagsInput::make('focus')
                            ->label('Focus areas')
                            ->placeholder('Add a focus area and press Enter')
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Cover image (optional)')
                            ->image()
                            ->disk('public')
                            ->directory('innovation')
                            ->imageEditor(),

                        FileUpload::make('video')
                            ->label('Video (optional)')
                            ->disk('public')
                            ->directory('innovation/videos')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->maxSize(204800)
                            ->helperText('MP4 or WebM, up to 200 MB. Plays muted and looped in place of the cover image; the image shows while it loads.'),

                        TextInput::make('sort_order')->numeric()->default(0)->required(),

                        Toggle::make('is_active')->label('Visible on the site')->default(true),
                    ]),

                Section::make('Hero banner')
                    ->description('The banner at the top of this area\'s page. A video plays behind the text; the photo shows while it loads. Leave both empty to keep the default banner photo.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('hero_image')
                            ->label('Hero image (optional)')
                            ->image()
                            ->disk('public')
                            ->directory('innovation/hero')
                            ->imageEditor()
                            ->helperText('Wide photo, at least 1920 × 800.'),

                        FileUpload::make('hero_video')
                            ->label('Hero video (optional)')
                            ->disk('public')
                            ->directory('innovation/hero')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->maxSize(204800)
                            ->helperText('MP4 or WebM, up to 200 MB. Plays muted and looped behind the banner text.'),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->searchable()->weight('semibold')->wrap(),
                TextColumn::make('department')->badge()->color('primary'),
                TextColumn::make('innovations_count')->counts('innovations')->label('Innovations'),
                TextColumn::make('projects_count')->counts('projects')->label('Projects'),
                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                // Its projects and innovations stay; they are simply left without an area.
                DeleteAction::make()
                    ->modalDescription('The area is removed from the site and the menu. Its projects and innovations are kept, without an area.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInnovationAreas::route('/'),
            'create' => CreateInnovationArea::route('/create'),
            'edit' => EditInnovationArea::route('/{record}/edit'),
        ];
    }
}
