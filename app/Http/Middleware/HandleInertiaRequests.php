<?php

namespace App\Http\Middleware;

use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => App::getLocale(),
            'available_locales' => ['en', 'de'],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'assigned_pending_count' => fn () => $request->user()
                ? Delegation::where('delegate_user_id', $request->user()->id)
                    ->where('status', Delegation::STATUS_INVITED)
                    ->count()
                : 0,
        ];
    }
}
