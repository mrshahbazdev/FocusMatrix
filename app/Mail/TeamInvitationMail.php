<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
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
        $relativeSigned = URL::signedRoute('team-invitations.accept', [
            'invitation' => $this->invitation,
        ], absolute: false);

        $acceptUrl = rtrim(config('app.url'), '/').$relativeSigned;

        return $this->markdown('emails.team-invitation', [
            'acceptUrl' => $acceptUrl,
        ])->subject(__('Team Invitation'));
    }
}
