<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\KillListItem;
use App\Models\OrgCheck;
use App\Models\SelfCheck;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LegalController extends Controller
{
    public function impressum(): InertiaResponse
    {
        return Inertia::render('Legal/Impressum');
    }

    public function privacy(): InertiaResponse
    {
        return Inertia::render('Legal/Privacy');
    }

    public function terms(): InertiaResponse
    {
        return Inertia::render('Legal/Terms');
    }

    public function cookies(): InertiaResponse
    {
        return Inertia::render('Legal/Cookies');
    }

    /**
     * GDPR Article 15/20: full data export (structured, machine-readable JSON).
     */
    public function exportData(Request $request): Response
    {
        $user = $request->user();

        $export = [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'format_version' => '1',
                'subject' => 'FocusMatrix personal data export (GDPR Art. 15/20)',
                'controller' => config('app.name'),
            ],
            'user' => $user->only(['id', 'name', 'email', 'job_title', 'locale', 'created_at', 'updated_at', 'email_verified_at']),
            'profile' => [
                'profile_photo_url' => $user->profile_photo_url,
                'current_team_id' => $user->current_team_id,
                'two_factor_enabled' => ! is_null($user->two_factor_confirmed_at ?? null),
            ],
            'tasks' => Task::where('user_id', $user->id)->get()->toArray(),
            'delegations' => Delegation::where('delegator_id', $user->id)->with('task:id,title')->get()->toArray(),
            'kill_list_items' => KillListItem::where('user_id', $user->id)->get()->toArray(),
            'self_checks' => SelfCheck::where('user_id', $user->id)->get()->toArray(),
            'org_checks' => OrgCheck::where('user_id', $user->id)->get()->toArray(),
            'integrations' => $user->integrations()->get()->map(fn ($i) => $i->only(['id', 'provider', 'account_email', 'created_at']))->values()->toArray(),
            'ai_settings' => $user->aiSetting ? collect($user->aiSetting->toArray())->except(['api_key_encrypted'])->toArray() : null,
            'sessions' => \DB::table('sessions')->where('user_id', $user->id)->select('id', 'ip_address', 'user_agent', 'last_activity')->get()->toArray(),
        ];

        $filename = 'focusmatrix-export-' . $user->id . '-' . now()->format('Y-m-d_His') . '.json';

        return response()
            ->make(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * GDPR Article 17: right to erasure.
     * Soft queues the account for deletion; hard-delete via Jetstream profile action.
     */
    public function requestDeletion(Request $request): RedirectResponse
    {
        $request->validate(['confirm' => ['required', 'in:delete my data']]);

        $user = $request->user();
        \Log::channel('daily')->warning('GDPR_DELETE_REQUEST', [
            'user_id' => $user->id, 'email' => $user->email, 'ip' => $request->ip(),
        ]);

        return redirect()->route('profile.show')
            ->with('success', __('Your deletion request has been recorded. Use the Delete Account section below to permanently remove your data.'));
    }
}
