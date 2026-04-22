<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_impressum_is_public(): void
    {
        $this->get('/legal/impressum')->assertOk();
    }

    public function test_privacy_is_public(): void
    {
        $this->get('/legal/privacy')->assertOk();
    }

    public function test_terms_is_public(): void
    {
        $this->get('/legal/terms')->assertOk();
    }

    public function test_cookies_is_public(): void
    {
        $this->get('/legal/cookies')->assertOk();
    }

    public function test_gdpr_export_requires_auth(): void
    {
        $this->get('/gdpr/export')->assertRedirect('/login');
    }

    public function test_gdpr_export_returns_json_for_authed_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/gdpr/export');
        $response->assertOk();
        $this->assertStringContainsString('application/json', $response->headers->get('content-type'));
        $payload = $response->json();
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('user', $payload);
        $this->assertSame($user->email, $payload['user']['email']);
    }

    public function test_gdpr_delete_requires_exact_confirmation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/gdpr/delete', ['confirm' => 'nope'])
            ->assertSessionHasErrors('confirm');
    }
}
