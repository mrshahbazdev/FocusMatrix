<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Laravel\Jetstream\TeamInvitation;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    public function __construct(TeamInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        $acceptUrl = route('accept-invitation', ['invitation' => $this->invitation->id]);

        return $this->markdown('emails.team-invitation', [
            'acceptUrl' => $acceptUrl,
        ])->subject(__('Team Invitation'));
    }
}
