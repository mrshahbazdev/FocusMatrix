<?php

namespace App\Services\Ai;

interface AiProvider
{
    /**
     * Send a single-turn prompt to the provider and return the plain-text response.
     *
     * @param  string  $system  System / instruction prompt
     * @param  string  $user    User message
     * @param  array   $options Optional: ['model' => string, 'json' => bool, 'temperature' => float]
     */
    public function chat(string $system, string $user, array $options = []): string;

    /**
     * Lightweight connectivity check. Returns true on success, throws on failure.
     */
    public function ping(): bool;

    public function name(): string;
}
