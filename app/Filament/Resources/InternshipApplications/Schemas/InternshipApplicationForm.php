<?php

namespace App\Filament\Resources\InternshipApplications\Schemas;

use App\Models\InternshipApplication;
use App\Support\Vocabulary;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** What the applicant sent (read only), then how the office is handling it. */
class InternshipApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Applicant')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('phone')->disabled(),
                    ]),

                Section::make('Academic')
                    ->columns(3)
                    ->schema([
                        TextInput::make('university')->disabled()->columnSpan(2),
                        TextInput::make('year_level')
                            ->label('Year of study')
                            ->formatStateUsing(fn (?string $state) => Vocabulary::label('internship_years', $state) ?? $state)
                            ->disabled(),
                        TextInput::make('department')->disabled(),
                        TextInput::make('programme')->disabled(),
                        TextInput::make('student_id')->label('Student ID')->placeholder('Not given')->disabled(),
                        TextInput::make('cgpa')->label('CGPA')->placeholder('Not given')->disabled(),
                    ]),

                Section::make('The internship')
                    ->columns(3)
                    ->schema([
                        TextInput::make('track')
                            ->formatStateUsing(fn (?string $state) => Vocabulary::label('internship_tracks', $state) ?? $state)
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('duration_months')
                            ->label('Duration')
                            ->formatStateUsing(fn ($state) => Vocabulary::label('internship_durations', (string) $state) ?? $state)
                            ->disabled(),
                        TextInput::make('start_date')
                            ->label('Preferred start')
                            ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('j M Y') : null)
                            ->disabled(),
                        TextInput::make('mode')
                            ->formatStateUsing(fn (?string $state) => Vocabulary::label('internship_modes', $state) ?? $state)
                            ->disabled(),
                        Textarea::make('skills')->rows(2)->placeholder('Not given')->disabled()->columnSpanFull(),
                        Textarea::make('motivation')->rows(5)->disabled()->columnSpanFull(),
                        FileUpload::make('cv')
                            ->label('CV')
                            ->disk('public')
                            ->downloadable()
                            ->openable()
                            ->disabled()
                            ->columnSpan(2),
                        TextInput::make('portfolio_url')
                            ->label('Portfolio / LinkedIn')
                            ->url()
                            ->placeholder('Not given')
                            ->disabled(),
                    ]),

                Section::make('Handling')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->required()
                            ->native(false)
                            ->options(InternshipApplication::STATUSES),

                        DateTimePicker::make('reviewed_at')->label('Reviewed at')->seconds(false),

                        Textarea::make('admin_notes')->label('Internal notes')->rows(4)->columnSpanFull(),
                    ]),
            ]);
    }
}
