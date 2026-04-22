<?php

namespace Tests\Feature;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationsWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_integrations_page_renders_and_generates_ics_token(): void
    {
        $user = User::factory()->create(['ics_token' => null]);
        $this->actingAs($user)->get('/integrations')->assertOk();
        $this->assertNotNull($user->fresh()->ics_token);
    }

    public function test_ics_feed_returns_calendar_for_valid_token(): void
    {
        $user = User::factory()->create(['ics_token' => 'abc123abc123abc123abc123abc123']);

        $response = $this->get('/calendar/ics/' . $user->ics_token);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('END:VCALENDAR', $response->getContent());
    }

    public function test_ics_feed_returns_404_for_bad_token(): void
    {
        $this->get('/calendar/ics/nonexistent-token')->assertNotFound();
    }

    public function test_slack_webhook_rejects_wrong_url_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post('/integrations/webhook/slack', ['webhook_url' => 'https://example.com/not-slack'])
            ->assertRedirect();
        $this->assertDatabaseMissing('integrations', ['user_id' => $user->id, 'provider' => 'slack']);
    }

    public function test_slack_webhook_saves_valid_url(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/integrations/webhook/slack', [
            'webhook_url' => 'https://hooks.slack.com/services/T0/B0/abc',
            'label' => '#team-ops',
        ])->assertRedirect();

        $this->assertDatabaseHas('integrations', [
            'user_id' => $user->id,
            'provider' => Integration::PROVIDER_SLACK,
            'label' => '#team-ops',
        ]);
    }

    public function test_regenerate_ics_token_rotates_value(): void
    {
        $user = User::factory()->create(['ics_token' => 'original-token-xxxxxxxxxxxxxxxxxxxxx']);
        $this->actingAs($user)->post('/integrations/ics/regenerate')->assertRedirect();
        $this->assertNotSame('original-token-xxxxxxxxxxxxxxxxxxxxx', $user->fresh()->ics_token);
    }

    public function test_webhook_notifier_posts_to_slack_url(): void
    {
        Http::fake();
        $user = User::factory()->create();
        Integration::create([
            'user_id' => $user->id,
            'provider' => Integration::PROVIDER_SLACK,
            'meta' => ['webhook_url' => 'https://hooks.slack.com/services/T0/B0/abc'],
        ]);

        app(\App\Services\WebhookNotifier::class)->notify($user, 'Hello', 'Body');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'hooks.slack.com'));
    }
}
