<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientPortalWelcome extends Notification implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(private string $token)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Set up your UGV RICH client account')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your client account is ready. Use '.$notifiable->email.' to sign in and view your orders and invoices.')
            ->action('Set your password', route('en.password.reset', [
                'token' => $this->token,
                'email' => $notifiable->email,
            ]))
            ->line('This link expires in '.config('auth.passwords.users.expire').' minutes. You can request a new link from the sign-in page.');
    }
}
