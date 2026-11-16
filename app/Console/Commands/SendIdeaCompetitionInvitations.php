<?php

namespace App\Console\Commands;

use App\Mail\IdeaCompetitionInvitation;
use App\Models\IdeaSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendIdeaCompetitionInvitations extends Command
{
    protected $signature = 'ideas:send-competition-invitations
        {--dry-run : List recipients without sending or marking records}
        {--id=* : Send only these submission IDs}
        {--limit= : Maximum number of submissions to process}
        {--delay=1 : Seconds between emails (0 to 60)}';

    protected $description = 'Send the Innovation Idea Competition 2026 invitation to primary-selected teams';

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
            $this->error('Configure a delivery mailer before sending invitations.');
            return self::FAILURE;
        }

        // A shared lock prevents overlapping runs from sending the same campaign.
        $lock = Cache::lock('ideas:november-2026-competition-invitations', 86400);
        if (! $lock->get()) {

            return self::FAILURE;
        }

        try {
            $query = IdeaSubmission::query()->where('status', 'primary_selected')->whereNull('competition_invitation_sent_at')
                ->when($ids, fn ($query) => $query->whereIn('id', $ids))
                ->orderBy('id');
            $sent = $failed = $processed = 0;

            foreach ($query->lazyById(100) as $submission) {
                if ($limit !== null && $processed >= (int) $limit) {
                    break;
                }
                $processed++;

                if (! filter_var($submission->email, FILTER_VALIDATE_EMAIL)) {
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
                    Mail::to($submission->email)->send(new IdeaCompetitionInvitation($submission));
                    $submission->forceFill(['competition_invitation_sent_at' => now()])->save();
                    $sent++;

                } catch (Throwable) {
                    $failed++;
                    $this->error("Failed to send {$submission->reference}.");
                }
            }

            $this->info("Processed: {$processed}; sent: {$sent}; failed: {$failed}.");

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
