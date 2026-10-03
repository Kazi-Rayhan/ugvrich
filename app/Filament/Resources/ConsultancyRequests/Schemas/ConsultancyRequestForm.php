<?php

namespace App\Filament\Resources\ConsultancyRequests\Schemas;

use App\Models\ConsultancyRequest;
use App\Support\MeetingSlots;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConsultancyRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Requester')
                    ->description('Submitted through the public consultancy request form.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(150),
                        Select::make('organization')
                            ->label('Organization type')
                            ->options(fn () => ConsultancyRequest::organizationOptions())
                            ->native(false),
                        TextInput::make('email')->email()->maxLength(180),
                        TextInput::make('phone')->tel()->maxLength(40),
                        Select::make('service_category_id')
                            ->label('Area of consultancy')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Requirement')
                    ->schema([
                        TextInput::make('area_of_interest')
                            ->label('Area of interest (free text)')
                            ->maxLength(180),

                        Textarea::make('requirement')
                            ->rows(8)
                            ->columnSpanFull(),

                        FileUpload::make('document')
                            ->label('Attached document')
                            ->disk('public')
                            ->directory('consultancy-requests')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Requested meeting')
                    ->description('The day and half-hour slot the requester asked for. The office is closed on Thursday and Friday; slots run 09:00 to 20:00.')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('preferred_date')
                            ->label('Preferred date')
                            ->native(false)
                            ->disabledDates(fn () => [])
                            ->helperText('Thursday and Friday are closed days.'),

                        Select::make('preferred_slot')
                            ->label('Preferred slot')
                            ->native(false)
                            ->options(fn () => collect(MeetingSlots::all())->mapWithKeys(
                                fn ($label, $value) => [$value => str_replace('-', ' – ', $value)],
                            )->all()),
                    ]),

                Section::make('Handling')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->required()
                            ->default('new')
                            ->options([
                                'new' => 'New',
                                'in_review' => 'In review',
                                'proposal_sent' => 'Proposal sent',
                                'accepted' => 'Accepted',
                                'declined' => 'Declined',
                                'closed' => 'Closed',
                            ])
                            ->native(false),

                        DateTimePicker::make('handled_at')
                            ->label('Handled at')
                            ->seconds(false),

                        Textarea::make('admin_notes')
                            ->label('Internal notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
