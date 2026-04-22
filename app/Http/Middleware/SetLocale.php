<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'de'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        App::setLocale($locale);
        $request->attributes->set('locale', $locale);
        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $fromQuery = $request->query('lang');
        if ($fromQuery && in_array($fromQuery, self::SUPPORTED, true)) {
            $request->session()->put('locale', $fromQuery);
            if ($request->user()) {
                $request->user()->forceFill(['locale' => $fromQuery])->save();
            }
            return $fromQuery;
        }

        if ($request->user() && in_array($request->user()->locale, self::SUPPORTED, true)) {
            return $request->user()->locale;
        }

        $fromSession = $request->session()->get('locale');
        if ($fromSession && in_array($fromSession, self::SUPPORTED, true)) {
            return $fromSession;
        }

        $preferred = substr((string) $request->getPreferredLanguage(self::SUPPORTED), 0, 2);
        return in_array($preferred, self::SUPPORTED, true) ? $preferred : 'en';
    }
}
