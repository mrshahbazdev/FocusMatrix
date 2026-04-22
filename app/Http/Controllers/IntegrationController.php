<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\GoogleCalendarService;
use App\Services\WebhookNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function __construct(
        private GoogleCalendarService $google,
        private WebhookNotifier $notifier,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $google = $user->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();
        $slack = $user->integrations()
            ->where('provider', Integration::PROVIDER_SLACK)
            ->first();
        $teams = $user->integrations()
            ->where('provider', Integration::PROVIDER_TEAMS)
            ->first();

        if (! $user->ics_token) {
            $user->forceFill(['ics_token' => Str::random(48)])->save();
        }

        return Inertia::render('Integrations/Index', [
            'google' => $google ? [
                'connected' => true,
                'account_email' => $google->account_email,
                'last_synced_at' => $google->last_synced_at,
                'expires_at' => $google->expires_at,
            ] : ['connected' => false],
            'google_configured' => $this->google->isConfigured(),
            'slack' => $slack ? [
                'connected' => true,
                'label' => $slack->label,
                'last_synced_at' => $slack->last_synced_at,
                'webhook_preview' => $this->maskUrl($slack->meta['webhook_url'] ?? ''),
            ] : ['connected' => false],
            'teams' => $teams ? [
                'connected' => true,
                'label' => $teams->label,
                'last_synced_at' => $teams->last_synced_at,
                'webhook_preview' => $this->maskUrl($teams->meta['webhook_url'] ?? ''),
            ] : ['connected' => false],
            'ics' => [
                'url' => url('/calendar/ics/' . $user->ics_token),
                'webcal_url' => 'webcal://' . request()->getHost() . '/calendar/ics/' . $user->ics_token,
            ],
        ]);
    }

    public function connectWebhook(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['slack', 'teams'], true), 404);

        $data = $request->validate([
            'webhook_url' => ['required', 'url', 'max:500'],
            'label' => ['nullable', 'string', 'max:60'],
        ]);

        $pattern = $provider === 'slack'
            ? '#^https://hooks\.slack\.com/services/#'
            : '#^https://[^.]+\.webhook\.office\.com/webhookb2/#';
        if (! preg_match($pattern, $data['webhook_url'])) {
            return back()->with('error', ucfirst($provider) . " webhook URL format looks wrong.");
        }

        $existing = $request->user()->integrations()->where('provider', $provider)->first();
        $meta = $existing ? (array) $existing->meta : [];
        $meta['webhook_url'] = $data['webhook_url'];

        Integration::updateOrCreate(
            ['user_id' => $request->user()->id, 'provider' => $provider],
            [
                'label' => $data['label'] ?? ($provider === 'slack' ? 'Slack' : 'Microsoft Teams'),
                'meta' => $meta,
                'last_synced_at' => null,
            ]
        );

        return back()->with('success', ucfirst($provider) . ' webhook saved.');
    }

    public function testWebhook(Request $request, string $provider): JsonResponse
    {
        abort_unless(in_array($provider, ['slack', 'teams'], true), 404);
        $data = $request->validate(['webhook_url' => ['required', 'url', 'max:500']]);
        return response()->json($this->notifier->test($provider, $data['webhook_url']));
    }

    public function disconnectWebhook(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['slack', 'teams'], true), 404);
        $request->user()->integrations()->where('provider', $provider)->delete();
        return back()->with('success', ucfirst($provider) . ' webhook removed.');
    }

    public function regenerateIcsToken(Request $request)
    {
        $request->user()->forceFill(['ics_token' => Str::random(48)])->save();
        return back()->with('success', 'Calendar feed URL regenerated. Update it in your calendar app.');
    }

    private function maskUrl(string $url): string
    {
        if (! $url) return '';
        $len = strlen($url);
        if ($len <= 40) return $url;
        return substr($url, 0, 30) . '…' . substr($url, -8);
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
