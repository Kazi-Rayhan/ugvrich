<?php

namespace Tests\Feature;

use App\Mail\IdeaSelectionNotice;
use App\Models\IdeaSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IdeaSelectionNoticeTest extends TestCase
{
    use RefreshDatabase;

    private function submission(string $email = 'innovator@example.com'): IdeaSubmission
    {
        return IdeaSubmission::create(['name' => 'Team Innovation', 'email' => $email, 'title' => 'A greener campus']);
    }

    public function test_notice_contains_personalised_thanks_and_both_dates(): void
    {
        $submission = $this->submission();
        $mail = new IdeaSelectionNotice($submission);
        $mail->assertSeeInHtml('Team Innovation');
        $mail->assertSeeInHtml('A greener campus');
        $mail->assertSeeInHtml($submission->reference);
        $mail->assertSeeInHtml('Primary selection result:');
        $mail->assertSeeInHtml('16 November 2026, by 12:00 PM (Bangladesh time)');
        $mail->assertSeeInHtml('Final Round:');
        $mail->assertSeeInHtml('20 November 2026');
        $mail->assertSeeInHtml('email and SMS');
    }

    public function test_dry_run_does_not_send_or_mark_submissions(): void
    {
        Mail::fake();
        $submission = $this->submission();
        $this->artisan('ideas:send-selection-notices', ['--dry-run' => true])->assertSuccessful();
        Mail::assertNothingSent();
        $this->assertNull($submission->fresh()->selection_notice_sent_at);
    }

    public function test_sends_one_at_a_time_and_reruns_skip_successful_submissions(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $first = $this->submission();
        $second = $this->submission('second@example.com');
        $this->artisan('ideas:send-selection-notices', ['--delay' => 0, '--limit' => 1])->assertSuccessful();
        Mail::assertSent(IdeaSelectionNotice::class, 1);
        $this->assertNotNull($first->fresh()->selection_notice_sent_at);
        $this->assertNull($second->fresh()->selection_notice_sent_at);
        $this->artisan('ideas:send-selection-notices', ['--delay' => 0])->assertSuccessful();
        Mail::assertSent(IdeaSelectionNotice::class, 2);
        $this->assertNotNull($second->fresh()->selection_notice_sent_at);
        $this->artisan('ideas:send-selection-notices', ['--delay' => 0])->assertSuccessful();
        Mail::assertSent(IdeaSelectionNotice::class, 2);
    }

    public function test_failed_email_is_not_marked_sent_and_next_recipient_is_processed(): void
    {
        config(['mail.default' => 'smtp']);
        $first = $this->submission();
        $second = $this->submission('second@example.com');
        Mail::shouldReceive('to')->once()->with($first->email)->andReturnSelf();
        Mail::shouldReceive('to')->once()->with($second->email)->andReturnSelf();
        Mail::shouldReceive('send')->once()->ordered()->andThrow(new \RuntimeException('Transport failed'));
        Mail::shouldReceive('send')->once()->ordered()->andReturnNull();
        $this->artisan('ideas:send-selection-notices', ['--delay' => 0])->assertFailed();
        $this->assertNull($first->fresh()->selection_notice_sent_at);
        $this->assertNotNull($second->fresh()->selection_notice_sent_at);
    }

    public function test_invalid_email_is_skipped_and_log_mailer_cannot_mark_records_sent(): void
    {
        Mail::fake();
        $submission = $this->submission('invalid');
        config(['mail.default' => 'smtp']);
        $this->artisan('ideas:send-selection-notices', ['--delay' => 0])->assertFailed();
        config(['mail.default' => 'log']);
        $this->artisan('ideas:send-selection-notices')->assertFailed();
        Mail::assertNothingSent();
        $this->assertNull($submission->fresh()->selection_notice_sent_at);
    }
}
