<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Jetstream;

class AcceptInvitationController extends Controller
{
    public function accept(Request $request, int $invitationId)
    {
        $model = Jetstream::teamInvitationModel();
        $invitation = $model::find($invitationId);

        if (! $invitation) {
            return redirect(config('fortify.home'))
                ->banner(__('This invitation has already been accepted or cancelled.'));
        }

        if ($request->user()->email !== $invitation->email) {
            abort(403, __('This invitation was sent to a different email address.'));
        }

        app(AddsTeamMembers::class)->add(
            $invitation->team->owner,
            $invitation->team,
            $invitation->email,
            $invitation->role
        );

        $invitation->delete();

        return redirect(config('fortify.home'))
            ->banner(__('Great! You have accepted the invitation to join the :team team.', [
                'team' => $invitation->team->name,
            ]));
    }
}
