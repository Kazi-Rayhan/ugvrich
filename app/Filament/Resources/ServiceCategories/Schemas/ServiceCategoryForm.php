<?php

namespace App\Filament\Resources\ServiceCategories\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\ServiceCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceCategoryForm
{
    /** Icon keys understood by the public site's x-ui-icon component. */
    public const ICONS = [
        'wrench' => 'Wrench (engineering)',
        'cpu' => 'Chip (ICT & digital)',
        'chart' => 'Chart (business)',
        'academic' => 'Academic cap (education)',
        'users' => 'People (social science)',
        'beaker' => 'Beaker (research)',
        'sparkles' => 'Sparkles (innovation)',
        'briefcase' => 'Briefcase (consultancy)',
        'globe' => 'Globe (hub)',
        'building' => 'Building',
        'lightbulb' => 'Lightbulb',
        'shield' => 'Shield',
        'target' => 'Target',
        'link' => 'Link (collaboration)',
        'handshake' => 'Handshake (partnership)',
        'star' => 'Star',
        'compass' => 'Compass',
        'document' => 'Document',
        'grid' => 'Grid',
        'rocket' => 'Rocket (startup)',
        'bolt' => 'Bolt (energy)',
        'heart' => 'Heart (health)',
        'cog' => 'Cog (mechanical)',
        'chat' => 'Chat (language)',
        'leaf' => 'Leaf (environment)',
        'key' => 'Key (IP)',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Bilingual::tabs(ServiceCategory::class, [
                    Section::make('Area')
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(150)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(150)
                                ->unique(ignoreRecord: true)
                                ->helperText('Used in the public URL: /services/{slug}'),

                            Select::make('department')
                                ->label('Sector')
                                ->native(false)
                                ->searchable()
                                ->options(config('rich.departments'))
                                ->helperText('The department that runs this service. Shown on the service pages beneath it.'),

                            TextInput::make('tagline')
                                ->maxLength(150)
                                ->helperText('Short line shown on cards and in the navigation menu.'),

                            Select::make('icon')
                                ->options(self::ICONS)
                                ->native(false)
                                ->searchable(),

                            Textarea::make('description')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Display')
                        ->columns(3)
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->disk('public')
                                ->directory('service-categories')
                                ->imageEditor(),

                            FileUpload::make('video')
                                ->label('Video (optional)')
                                ->disk('public')
                                ->directory('service-categories/videos')
                                ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                ->maxSize(204800)
                                ->helperText('MP4 or WebM, up to 200 MB. Plays muted and looped in place of the image; the image shows while it loads.'),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->required(),

                            Toggle::make('is_active')
                                ->label('Visible on the site')
                                ->default(true),
                        ]),
                ]),
            ]);
    }
}
