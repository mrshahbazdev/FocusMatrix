<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use App\Models\User;
use RuntimeException;

class AiManager
{
    /**
     * Resolve the AI provider for a given user, or null if not configured / quota exhausted.
     */
    public function for(User $user): ?AiProvider
    {
        $setting = $user->aiSetting;
        if (! $setting || ! $setting->enabled || ! $setting->hasKey()) {
            return null;
        }
        if ($setting->remainingQuota() <= 0) {
            return null;
        }
        return $this->makeProvider($setting->provider, $setting->getApiKey(), $setting->model ?? AiSetting::DEFAULT_MODELS[$setting->provider] ?? 'gemini-2.0-flash');
    }

    /**
     * Factory for arbitrary provider (used by the "Test connection" button).
     */
    public function makeProvider(string $provider, string $apiKey, ?string $model = null): AiProvider
    {
        $model = $model ?? (AiSetting::DEFAULT_MODELS[$provider] ?? 'gemini-2.0-flash');
        return match ($provider) {
            'gemini' => new GeminiProvider($apiKey, $model),
            'openai' => new OpenAiProvider($apiKey, $model),
            'anthropic' => new AnthropicProvider($apiKey, $model),
            default => throw new RuntimeException("Unknown AI provider: {$provider}"),
        };
    }

    /**
     * Run a prompt for a user and register quota. Returns null if AI unavailable.
     */
    public function promptFor(User $user, string $system, string $user_msg, array $options = []): ?string
    {
        $provider = $this->for($user);
        if (! $provider) {
            return null;
        }
        try {
            $out = $provider->chat($system, $user_msg, $options);
            $user->aiSetting->registerCall();
            return $out;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function promptJsonFor(User $user, string $system, string $user_msg): ?array
    {
        $raw = $this->promptFor($user, $system, $user_msg, ['json' => true]);
        if (! $raw) {
            return null;
        }
        $raw = preg_replace('/^```json\s*|\s*```$/m', '', $raw);
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }
}
