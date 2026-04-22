<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function __construct(private GoogleCalendarService $google)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $google = $user->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();

        return Inertia::render('Integrations/Index', [
            'google' => $google ? [
                'connected' => true,
                'account_email' => $google->account_email,
                'last_synced_at' => $google->last_synced_at,
                'expires_at' => $google->expires_at,
            ] : ['connected' => false],
            'google_configured' => $this->google->isConfigured(),
        ]);
    }

    public function connectGoogle(Request $request)
    {
        if (! $this->google->isConfigured()) {
            return redirect()->route('integrations.index')
                ->with('error', 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.');
        }

        $state = Str::random(32);
        $request->session()->put('google_oauth_state', $state);
        return redirect()->away($this->google->authUrl($state));
    }

    public function callbackGoogle(Request $request)
    {
        if ($request->query('error')) {
            return redirect()->route('integrations.index')
                ->with('error', 'Google denied the request: ' . $request->query('error'));
        }

        $sessionState = $request->session()->pull('google_oauth_state');
        if ($sessionState && $request->query('state') !== $sessionState) {
            return redirect()->route('integrations.index')
                ->with('error', 'OAuth state mismatch.');
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()->route('integrations.index')
                ->with('error', 'Missing authorization code.');
        }

        try {
            $this->google->handleCallback($request->user(), $code);
        } catch (\Throwable $e) {
            return redirect()->route('integrations.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('integrations.index')
            ->with('success', 'Google Calendar connected.');
    }

    public function disconnectGoogle(Request $request)
    {
        $request->user()->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->delete();

        return redirect()->route('integrations.index')
            ->with('success', 'Google Calendar disconnected.');
    }
}
