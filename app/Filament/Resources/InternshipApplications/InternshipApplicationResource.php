<?php

namespace App\Filament\Resources\InternshipApplications;

use App\Filament\Resources\InternshipApplications\Pages\EditInternshipApplication;
use App\Filament\Resources\InternshipApplications\Pages\ListInternshipApplications;
use App\Filament\Resources\InternshipApplications\Schemas\InternshipApplicationForm;
use App\Filament\Resources\InternshipApplications\Tables\InternshipApplicationsTable;
use App\Models\InternshipApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** Internship applications from the public /internship form. Read and handled here; never created here. */
class InternshipApplicationResource extends Resource
{
    protected static ?string $model = InternshipApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Internship application';

    protected static ?string $pluralModelLabel = 'Internship applications';

    public static function form(Schema $schema): Schema
    {
        return InternshipApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InternshipApplicationsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInternshipApplications::route('/'),
            'edit' => EditInternshipApplication::route('/{record}/edit'),
        ];
    }
}
