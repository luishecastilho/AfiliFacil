<?php

namespace App\Notifications;

use App\Models\Import;
use App\Models\InvoiceFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicesGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Import $import, public readonly InvoiceFile $zipFile)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoices ready for import #{$this->import->id}")
            ->line('Your ZIP of generated invoices is ready to download.')
            ->action('Download ZIP', route('imports.show', $this->import));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'import_id' => $this->import->id,
            'invoice_file_id' => $this->zipFile->id,
        ];
    }
}
