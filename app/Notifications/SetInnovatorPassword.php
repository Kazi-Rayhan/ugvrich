<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an idea creates a new innovator account: the idea is in, and here
 * is the link to choose a password and follow it from the dashboard.
 */
class SetInnovatorPassword extends Notification
{
    public function __construct(
        protected string $token,
        protected string $ideaTitle,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('innovator.password.set', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject(__('innovator.mail.subject'))
            ->greeting(__('innovator.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('innovator.mail.received', ['title' => $this->ideaTitle]))
            ->line(__('innovator.mail.account'))
            ->action(__('innovator.mail.action'), $url)
            ->line(__('innovator.mail.expiry'));
    }
}
