<?php

namespace App\Mail;

use App\Models\IdeaSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\HtmlString;

class IdeaCompetitionInvitation extends Mailable
{
    public function __construct(public IdeaSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Congratulations! You Have Been Selected for the Innovation Idea Competition 2026 – UGV-RICH');
    }

    public function build(): static
    {
        return $this->text(new HtmlString(file_get_contents(resource_path('views/mail/ideas/competition-invitation.txt'))));
    }
}
