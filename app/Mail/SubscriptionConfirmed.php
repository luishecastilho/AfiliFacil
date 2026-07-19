<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user, public readonly string $plan)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo ao plano '.config("plans.{$this->plan}.name"),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-confirmed',
            with: [
                'planName' => config("plans.{$this->plan}.name"),
                'nfLimit' => config("plans.{$this->plan}.nf_limit"),
                'userName' => $this->user->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
