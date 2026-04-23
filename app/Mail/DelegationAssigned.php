<?php

namespace App\Mail;

use App\Models\Delegation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DelegationAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Delegation $delegation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.delegation_assigned.subject', [
                'delegator' => $this->delegation->delegator?->name ?? '—',
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.delegation-assigned',
            with: [
                'delegation' => $this->delegation->fresh(['task', 'delegator']),
                'acceptUrl' => url('/assigned/' . $this->delegation->invite_token . '/accept'),
                'declineUrl' => url('/assigned/' . $this->delegation->invite_token . '/decline'),
                'inboxUrl' => url('/assigned'),
            ],
        );
    }
}
