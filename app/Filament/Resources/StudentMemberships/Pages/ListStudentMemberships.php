<?php

namespace App\Filament\Resources\StudentMemberships\Pages;

use App\Filament\Resources\StudentMemberships\StudentMembershipResource;
use Filament\Resources\Pages\ListRecords;

class ListStudentMemberships extends ListRecords
{
    protected static string $resource = StudentMembershipResource::class;
}
