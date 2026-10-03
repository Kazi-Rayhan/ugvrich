<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Service;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Bilingual::tabs(Service::class, [
                    Section::make('Service')
                        ->columns(2)
                        ->schema([
                            Select::make('service_category_id')
                                ->label('Area of consultancy')
                                ->relationship('category', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),

                            TextInput::make('name')
                                ->required()
                                ->maxLength(150)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')->required()->maxLength(150),

                            Select::make('department')
                                ->label('Delivered by')
                                ->native(false)
                                ->searchable()
                                ->options(config('rich.departments'))
                                ->helperText('Groups the service under a department on the consultancy page.'),

                            Textarea::make('description')
                                ->rows(3)
                                ->columnSpanFull()
                                ->helperText('One or two lines. This is the card text on the listing pages.'),

                            Textarea::make('body')
                                ->label('Full description')
                                ->rows(8)
                                ->columnSpanFull()
                                ->helperText('The service page. Leave a blank line between paragraphs. Empty means the page shows the short description alone.'),

                            TagsInput::make('highlights')
                                ->label('What the client gets')
                                ->placeholder('Add a point and press Enter')
                                ->columnSpanFull()
                                ->helperText('Listed beside the description on the service page.'),

                            FileUpload::make('image')
                                ->label('Image (optional)')
                                ->image()
                                ->disk('public')
                                ->directory('services')
                                ->imageEditor()
                                ->columnSpanFull(),

                            FileUpload::make('video')
                                ->label('Video (optional)')
                                ->disk('public')
                                ->directory('services/videos')
                                ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                ->maxSize(204800)
                                ->columnSpanFull()
                                ->helperText('MP4 or WebM, up to 200 MB. Plays muted and looped in the hero of the service page, in place of the image.'),

                            TextInput::make('sort_order')->numeric()->default(0)->required(),

                            Toggle::make('is_active')->label('Visible on the site')->default(true),
                        ]),
                ]),
            ]);
    }
}
