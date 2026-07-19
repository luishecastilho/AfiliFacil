<?php

namespace App\Notifications;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification
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
            ->subject("Import #{$this->import->id} completed")
            ->line("Your import of {$this->import->original_filename} has finished processing.")
            ->line("Valid rows: {$this->import->valid_rows}, Invalid: {$this->import->invalid_rows}, Duplicate: {$this->import->duplicate_rows}.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'import_id' => $this->import->id,
            'status' => $this->import->status->value,
        ];
    }
}
