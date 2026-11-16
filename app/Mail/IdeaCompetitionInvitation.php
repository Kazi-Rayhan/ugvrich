<?php

namespace App\Mail;

use App\Models\IdeaSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class IdeaCompetitionInvitation extends Mailable
{
    public function __construct(public IdeaSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Congratulations! You Have Been Selected for the Innovation Idea Competition 2026 – UGV-RICH');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.ideas.competition-invitation');
    }
}
