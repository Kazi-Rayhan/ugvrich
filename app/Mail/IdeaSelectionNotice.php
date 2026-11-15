<?php

namespace App\Mail;

use App\Models\IdeaSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class IdeaSelectionNotice extends Mailable
{
    public function __construct(public IdeaSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thank You for Your Idea Submission | Selection Dates - UGV RICH');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.ideas.selection-notice');
    }
}
