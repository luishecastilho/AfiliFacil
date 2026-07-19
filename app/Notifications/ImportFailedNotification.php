<?php

namespace App\Notifications;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFailedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Import $import)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Import #{$this->import->id} failed")
            ->line("Your import of {$this->import->original_filename} could not be processed.")
            ->line($this->import->error_message ?? 'An unknown error occurred.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'import_id' => $this->import->id,
            'error_message' => $this->import->error_message,
        ];
    }
}
