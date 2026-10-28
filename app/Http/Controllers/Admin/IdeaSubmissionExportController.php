<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Support\PdfExport;
use App\Http\Controllers\Controller;
use App\Models\IdeaSubmission;
use Illuminate\Http\Request;

/** The idea submissions report, opened from "Export PDF" on the admin list. */
class IdeaSubmissionExportController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->user()?->can('viewAny', IdeaSubmission::class), 403);

        $export = PdfExport::pull((string) $request->query('token'));
        abort_if($export === null, 410, 'This export has expired. Export again from the admin list.');

        // Kept in the order the table showed them.
        $order = array_flip($export['ids']);
        $ideas = IdeaSubmission::whereKey($export['ids'])->get()->sortBy(fn ($idea) => $order[$idea->id])->values();

        return view('exports.idea-submissions', [
            'ideas' => $ideas,
            'scope' => $export['scope'],
        ]);
    }
}
