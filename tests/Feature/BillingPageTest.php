<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_page_requires_auth(): void
    {
        $this->get('/billing')->assertRedirect('/login');
    }

    public function test_authed_user_sees_billing_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/billing')->assertOk();
    }
}
