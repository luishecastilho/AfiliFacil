<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerInvoiceNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your invoice #{$this->invoice->invoice_number} is ready")
            ->line("Your NF-e for reference month {$this->invoice->reference_month} has been issued.")
            ->line("Amount: R$ {$this->invoice->amount}");
    }
}
