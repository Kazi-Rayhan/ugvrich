<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * One notification for every research workflow event.
 *
 * Laravel's own notification system, on the `notifications` table that already
 * exists — not a second mechanism. One class rather than fifteen because every
 * one of these says the same three things: what happened, to which record, and
 * where to go and look.
 *
 * `database` always; `mail` only for the events that are worth an email. A
 * message for every status change would train people to ignore all of them, so
 * {@see self::worthEmailing()} is deliberately short.
 */
class ResearchEvent extends Notification
{
    use Queueable;

    /**
     * @param  string  $event     a key from research.events, e.g. 'idea.approved'
     * @param  string  $title     the record's own title, shown in the list
     * @param  string  $url       where the researcher should land
     * @param  string|null  $body optional detail — a reviewer's comment, say
     */
    public function __construct(
        public string $event,
        public string $title,
        public string $url,
        public ?string $body = null,
    ) {}

    /**
     * Events that justify interrupting somebody by email.
     *
     * Decisions and outcomes, not movements. "Your proposal was approved" is
     * worth an email; "your manuscript is now under review" is not.
     */
    protected function worthEmailing(): bool
    {
        return in_array($this->event, [
            'idea.approved',
            'idea.revision',
            'idea.rejected',
            'proposal.approved',
            'proposal.revision',
            'proposal.rejected',
            'funding.decided',
            'project.created',
            'project.status',
            'manuscript.revision',
            'manuscript.approved',
            'manuscript.accepted',
            'manuscript.published',
            'manuscript.rejected',
        ], true);
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        /* The database record is written whatever the account's preference is,
           so turning email off loses nothing — the notification is still there
           in the portal. Only the interruption is switched off. */
        $byEmail = $this->worthEmailing()
            && ($notifiable->email_notifications ?? true);

        return $byEmail ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $heading = __('research.events.'.$this->event);

        return (new MailMessage)
            ->subject($heading.' — '.$this->title)
            ->greeting(__('research.mail.greeting', ['name' => $notifiable->name]))
            ->line($heading.':')
            ->line('**'.$this->title.'**')
            ->when($this->body, fn (MailMessage $mail) => $mail->line($this->body))
            ->action(__('research.mail.action'), $this->url)
            ->line(__('research.mail.sign_off'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'title' => $this->title,
            'url' => $this->url,
            'body' => $this->body,
        ];
    }
}
