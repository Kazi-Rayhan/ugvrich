<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Bilingual::tabs(Post::class, [
                    Section::make('Item')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(200)
                                ->columnSpanFull()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->unique(ignoreRecord: true),

                            Select::make('type')
                                ->required()
                                ->default('news')
                                ->native(false)
                                ->live()
                                ->options([
                                    'news' => 'News',
                                    'event' => 'Event',
                                ]),

                            TextInput::make('category')
                                ->label('Label')
                                ->maxLength(80)
                                ->placeholder('Capacity Building, Partnership, Seminar...'),

                            TextInput::make('author')->maxLength(120),
                        ]),

                    Section::make('Event details')
                        ->columns(2)
                        ->visible(fn ($get) => $get('type') === 'event')
                        ->schema([
                            DateTimePicker::make('event_at')
                                ->label('Event date & time')
                                ->seconds(false),

                            TextInput::make('location')->maxLength(180),
                        ]),

                    Section::make('Content')
                        ->schema([
                            Textarea::make('excerpt')
                                ->rows(3)
                                ->maxLength(400)
                                ->helperText('Shown on cards and in search results.')
                                ->columnSpanFull(),

                            Textarea::make('body')
                                ->rows(14)
                                ->helperText('Separate paragraphs with a blank line.')
                                ->columnSpanFull(),

                            FileUpload::make('image')
                                ->label('Cover image')
                                ->image()
                                ->disk('public')
                                ->directory('posts')
                                ->imageEditor()
                                ->columnSpanFull(),
                        ]),

                    Section::make('Source & video')
                        ->description('Optional. Where the item was first published, and a video to show with it.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('reference_name')
                                ->label('Reference name')
                                ->placeholder('e.g. The Daily Star')
                                ->maxLength(180)
                                ->helperText('The outlet or organisation the item comes from.'),

                            TextInput::make('external_url')
                                ->label('External URL')
                                ->url()
                                ->maxLength(500)
                                ->placeholder('https://…')
                                ->helperText('Link to the original article or page.'),

                            TextInput::make('video_url')
                                ->label('Video URL')
                                ->url()
                                ->maxLength(500)
                                ->placeholder('https://www.youtube.com/watch?v=…')
                                ->helperText('YouTube or Vimeo links play on the page; any other link shows as a "Watch the video" button.')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Publishing')
                        ->columns(2)
                        ->schema([
                            DateTimePicker::make('published_at')
                                ->label('Publish at')
                                ->seconds(false)
                                ->default(now())
                                ->helperText('Leave empty to keep this as a draft.'),

                            Toggle::make('is_featured')->label('Feature on the homepage'),
                        ]),
                ]),
            ]);
    }
}
