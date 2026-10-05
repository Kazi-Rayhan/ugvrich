<?php

namespace App\Filament\Resources\IdeaSubmissions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IdeaSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Who submitted it')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('email')->email()->disabled(),

                        TextInput::make('role')
                            ->disabled()
                            ->formatStateUsing(fn (?string $state) => config('rich.idea_roles.'.$state, $state)),

                        TextInput::make('department')
                            ->disabled()
                            ->formatStateUsing(fn (?string $state) => config('rich.departments.'.$state, $state)),

                        TextInput::make('programme')->label('Programme / designation')->disabled(),
                    ]),

                Section::make('The idea')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->disabled()->columnSpanFull(),

                        TextInput::make('category')
                            ->disabled()
                            ->formatStateUsing(fn (?string $state) => config('rich.idea_categories.'.$state, $state))
                            ->placeholder('Not given'),

                        TextInput::make('user_id')
                            ->label('Innovator account')
                            ->disabled()
                            ->formatStateUsing(fn ($state, $record) => $record?->user?->email)
                            ->placeholder('No account'),
                        Textarea::make('problem')->rows(5)->disabled()->columnSpanFull(),
                        Textarea::make('solution')->rows(5)->disabled()->columnSpanFull(),
                        Textarea::make('beneficiaries')->rows(3)->disabled()->columnSpanFull(),
                        Textarea::make('resources_needed')->label('Support needed')->rows(3)->disabled()->columnSpanFull(),
                        TextInput::make('team_size')->disabled(),
                        TextInput::make('document')
                            ->label('Attached file')
                            ->disabled()
                            ->placeholder('No file attached'),
                    ]),

                Section::make('Handling')
                    ->columns(2)
                    ->schema([
                        Select::make('stage')
                            ->label('Journey stage')
                            ->required()
                            ->default('idea')
                            ->native(false)
                            ->options(config('rich.startup_stages')),

                        Select::make('status')
                            ->required()
                            ->default('new')
                            ->native(false)
                            ->options([
                                'new' => 'New',
                                'in_review' => 'In review',
                                'accepted' => 'Accepted',
                                'on_hold' => 'On hold',
                                'declined' => 'Declined',
                            ]),

                        DateTimePicker::make('reviewed_at')->label('Reviewed at')->seconds(false),

                        Textarea::make('admin_notes')->label('Internal notes')->rows(4)->columnSpanFull(),
                    ]),
            ]);
    }
}
