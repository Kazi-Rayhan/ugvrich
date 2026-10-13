<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** The "forgot password" email: a link to choose a new password, in the page's language. */
class ResetAccountPassword extends Notification
{
    public function __construct(protected string $token) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject(__('researcher.auth.mail_subject'))
            ->greeting(__('researcher.auth.mail_greeting', ['name' => $notifiable->name]))
            ->line(__('researcher.auth.mail_body'))
            ->action(__('researcher.auth.mail_action'), $url)
            ->line(__('researcher.auth.mail_expiry', ['minutes' => config('auth.passwords.users.expire')]))
            ->line(__('researcher.auth.mail_ignore'));
    }
}
