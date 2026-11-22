<?php

namespace App\Notifications;

use Filament\Notifications\Notification as PanelNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultancyUpdate extends Notification implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $url,
        public string $action,
        public bool $staff = false,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->staff ? ['database', 'mail'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject($this->title)->line($this->message)->action($this->action, $this->url);
    }

    public function toDatabase(object $notifiable): array
    {
        return PanelNotification::make()->title($this->title)->body($this->message)->getDatabaseMessage();
    }
}
