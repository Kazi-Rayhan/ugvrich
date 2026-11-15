<?php

namespace App\Console\Commands;

use App\Mail\IdeaSelectionNotice;
use App\Models\IdeaSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendIdeaSelectionNotices extends Command
{
    protected $signature = 'ideas:send-selection-notices
        {--dry-run : List recipients without sending or marking records}
        {--id=* : Send only these submission IDs}
        {--limit= : Maximum number of submissions to process}
        {--delay=1 : Seconds between emails (0 to 60)}';

    protected $description = 'Send the November 2026 thank-you and selection schedule to idea submitters, one email at a time';

    public function handle(): int
    {
        $delay = filter_var($this->option('delay'), FILTER_VALIDATE_INT);
        $limit = $this->option('limit');
        $ids = $this->option('id');

        if ($delay === false || $delay < 0 || $delay > 60 ||
            ($limit !== null && (! ctype_digit((string) $limit) || (int) $limit < 1)) ||
            collect($ids)->contains(fn ($id) => ! ctype_digit((string) $id) || (int) $id < 1)) {
        

            return self::FAILURE;
        }

        if (! $this->option('dry-run') && in_array(config('mail.default'), ['log', 'array'], true)) {
           

            return self::FAILURE;
        }

        // A shared lock prevents overlapping runs from sending the same campaign.
        $lock = Cache::lock('ideas:november-2026-selection-notices', 86400);
        if (! $lock->get()) {
            

            return self::FAILURE;
        }

        try {
            $query = IdeaSubmission::query()->whereNull('selection_notice_sent_at')
                ->when($ids, fn ($query) => $query->whereIn('id', $ids))
                ->orderBy('id');
            $sent = $failed = $processed = 0;

            foreach ($query->lazyById(100) as $submission) {
                if ($limit !== null && $processed >= (int) $limit) {
                    break;
                }
                $processed++;

                if (! filter_var($submission->email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn("Skipped {$submission->reference}: missing or invalid email.");
                    $failed++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("Would send {$submission->reference} to {$submission->email}");

                    continue;
                }

                if ($sent + $failed > 0 && $delay > 0) {
                    sleep($delay);
                }

                try {
                    Mail::to($submission->email)->send(new IdeaSelectionNotice($submission));
                    $submission->forceFill(['selection_notice_sent_at' => now()])->save();
                    $sent++;
                 
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                    $this->error("Failed {$submission->reference}; check the application log before retrying.");
                }
            }

            $this->info($this->option('dry-run')
                ? "Dry run complete: {$processed} submissions checked. No emails sent."
                : "Complete: {$sent} sent, {$failed} failed or invalid. Previously sent submissions were skipped.");

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
