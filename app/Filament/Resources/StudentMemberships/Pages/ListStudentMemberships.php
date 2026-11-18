<?php

namespace App\Filament\Resources\StudentMemberships\Pages;

use App\Filament\Resources\StudentMemberships\StudentMembershipResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListStudentMemberships extends ListRecords
{
    protected static string $resource = StudentMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->downloadExcel()),
        ];
    }

    protected function downloadExcel(): StreamedResponse
    {
        abort_unless(StudentMembershipResource::canViewAny(), 403);

        $query = $this->getTableQueryForExport();

        return response()->streamDownload(function () use ($query) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            try {
                $writer->addRow(Row::fromValues([
                    'Received', 'Reference', 'Name', 'Student ID', 'Department',
                    'Semester', 'Track', 'Phone', 'Email', 'Status',
                ]));

                foreach ($query->cursor() as $application) {
                    $values = [
                        $application->created_at?->format('Y-m-d H:i:s'),
                        $application->reference,
                        $application->name,
                        $application->student_id,
                        $application->department_name ?? $application->department,
                        $application->semester,
                        $application->track_label ?? $application->track,
                        $application->phone,
                        $application->email,
                        str($application->status)->replace('_', ' ')->title()->toString(),
                    ];

                    // Write applicant input as text to preserve leading zeros and prevent formulas.
                    $writer->addRow(new Row(array_map(fn ($value) => new StringCell((string) $value, null), $values)));
                }
            } finally {
                $writer->close();
            }
        }, 'student-memberships-'.now()->toDateString().'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
