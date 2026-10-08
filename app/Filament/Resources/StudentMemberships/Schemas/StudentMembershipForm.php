<?php

namespace App\Filament\Resources\StudentMemberships\Schemas;

use App\Support\Vocabulary;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentMembershipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Application')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('student_id')->label('Student ID')->disabled(),
                        TextInput::make('semester')->disabled(),
                        TextInput::make('department')
                            ->formatStateUsing(fn (?string $state) => Vocabulary::label('departments', $state) ?? $state)
                            ->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('track')
                            ->label('Internship track')
                            ->formatStateUsing(fn (?string $state) => Vocabulary::label('internship_tracks', $state) ?? $state)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Handling')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->required()
                            ->native(false)
                            ->options([
                                'new' => 'New',
                                'in_review' => 'In review',
                                'accepted' => 'Accepted',
                                'declined' => 'Declined',
                            ]),

                        Textarea::make('admin_notes')->label('Internal notes')->rows(4)->columnSpanFull(),
                    ]),
            ]);
    }
}
