<?php

namespace Tests\Feature;

use App\Models\AiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_page_requires_auth(): void
    {
        $this->get('/settings/ai')->assertRedirect('/login');
    }

    public function test_authed_user_can_view_ai_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/settings/ai')->assertOk();
    }

    public function test_user_can_save_api_key_encrypted(): void
    {
        $user = User::factory()->create();
        $rawKey = str_repeat('x', 32);

        $this->actingAs($user)->put('/settings/ai', [
            'provider' => 'gemini',
            'api_key' => $rawKey,
            'model' => 'gemini-2.0-flash',
            'enabled' => true,
            'monthly_limit' => 200,
        ])->assertRedirect();

        $setting = AiSetting::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('gemini', $setting->provider);
        $this->assertNotSame($rawKey, $setting->api_key_encrypted);
        $this->assertSame($rawKey, Crypt::decryptString($setting->api_key_encrypted));
    }

    public function test_user_can_remove_api_key(): void
    {
        $user = User::factory()->create();
        AiSetting::create([
            'user_id' => $user->id,
            'provider' => 'gemini',
            'api_key_encrypted' => Crypt::encryptString('abc'),
            'enabled' => true,
        ]);

        $this->actingAs($user)->delete('/settings/ai')->assertRedirect();

        $setting = AiSetting::where('user_id', $user->id)->first();
        $this->assertNull($setting->api_key_encrypted);
        $this->assertFalse((bool) $setting->enabled);
    }
}
