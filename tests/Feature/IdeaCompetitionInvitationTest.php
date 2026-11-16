<?php

namespace Tests\Feature;

use App\Mail\IdeaCompetitionInvitation;
use App\Models\IdeaSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IdeaCompetitionInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function submission(string $status): IdeaSubmission
    {
        return IdeaSubmission::create([
            'name' => 'Team Innovation', 'email' => 'team@example.com',
            'title' => 'A greener campus', 'status' => $status,
            'selection_notice_sent_at' => now(),
        ]);
    }

    public function test_invitation_contains_the_supplied_competition_details(): void
    {
        $mail = new IdeaCompetitionInvitation($this->submission('primary_selected'));
        $mail->assertHasSubject('Congratulations! You Have Been Selected for the Innovation Idea Competition 2026 – UGV-RICH');
        foreach (['Dear Selected Team', 'Sunday, 22nd November 2026', 'Room No. 302',
            'Room No. 401 (Library)', '10:00 AM sharp', 'both PDF and PPTX',
            'rich@ugv.edu.bd', '20th November 2026', '5 minutes only', 'Jannatul Tajri'] as $text) {
            $mail->assertSeeInHtml($text);
            $mail->assertSeeInText($text);
        }
    }

    public function test_only_primary_selected_teams_receive_it_and_reruns_skip_sent_invitations(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $selected = $this->submission('primary_selected');
        foreach (['new', 'in_review', 'accepted', 'final_selected', 'declined', 'on_hold'] as $status) {
            $this->submission($status);
        }

        $this->artisan('ideas:send-competition-invitations', ['--delay' => 0])->assertSuccessful();
        Mail::assertSent(IdeaCompetitionInvitation::class, 1);
        $this->assertNotNull($selected->fresh()->competition_invitation_sent_at);
        $this->assertSame(1, IdeaSubmission::whereNotNull('competition_invitation_sent_at')->count());

        $this->artisan('ideas:send-competition-invitations', ['--delay' => 0])->assertSuccessful();
        Mail::assertSent(IdeaCompetitionInvitation::class, 1);
    }

    public function test_dry_run_does_not_send_or_mark_the_invitation(): void
    {
        Mail::fake();
        $selected = $this->submission('primary_selected');
        $this->artisan('ideas:send-competition-invitations', ['--dry-run' => true])
            ->expectsOutput("Would send {$selected->reference} to {$selected->email}")
            ->assertSuccessful();
        Mail::assertNothingSent();
        $this->assertNull($selected->fresh()->competition_invitation_sent_at);
    }
}
