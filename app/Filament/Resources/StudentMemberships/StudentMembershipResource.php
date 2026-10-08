<?php

namespace App\Filament\Resources\StudentMemberships;

use App\Filament\Resources\StudentMemberships\Pages\EditStudentMembership;
use App\Filament\Resources\StudentMemberships\Pages\ListStudentMemberships;
use App\Filament\Resources\StudentMemberships\Schemas\StudentMembershipForm;
use App\Filament\Resources\StudentMemberships\Tables\StudentMembershipsTable;
use App\Models\StudentMembership;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentMembershipResource extends Resource
{
    protected static ?string $model = StudentMembership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Internship application';

    protected static ?string $pluralModelLabel = 'Internship applications';

    public static function form(Schema $schema): Schema
    {
        return StudentMembershipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentMembershipsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentMemberships::route('/'),
            'edit' => EditStudentMembership::route('/{record}/edit'),
        ];
    }
}
