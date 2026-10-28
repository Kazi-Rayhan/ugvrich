<?php

namespace App\Filament\Resources\IdeaSubmissions\Pages;

use App\Filament\Resources\IdeaSubmissions\IdeaSubmissionResource;
use App\Filament\Support\PdfExport;
use Filament\Resources\Pages\ListRecords;

class ListIdeaSubmissions extends ListRecords
{
    protected static string $resource = IdeaSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        // Exports what the table shows: its filters, search and sort.
        return [PdfExport::headerAction('exports.idea-submissions')];
    }
}
