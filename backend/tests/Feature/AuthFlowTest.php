<?php

namespace Tests\Feature;

use App\Mail\MagicLinkMail;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_link_creates_token_and_sends_mail(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/request-link', [
            'email' => 'driver@example.com',
        ]);

        $response->assertOk()->assertJson(['message' => 'Check your email.']);
        $this->assertDatabaseCount('magic_links', 1);
        Mail::assertSent(MagicLinkMail::class);
    }

    public function test_request_link_response_does_not_leak_account_existence(): void
    {
        Mail::fake();

        // Existing user
        User::factory()->create(['email' => 'known@example.com']);

        $known = $this->postJson('/api/auth/request-link', ['email' => 'known@example.com']);
        $unknown = $this->postJson('/api/auth/request-link', ['email' => 'unknown@example.com']);

        $known->assertOk();
        $unknown->assertOk();
        $this->assertSame($known->json('message'), $unknown->json('message'));
    }

    public function test_request_link_validates_email(): void
    {
        $this->postJson('/api/auth/request-link', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }

    public function test_verify_consumes_token_and_returns_sanctum_token(): void
    {
        Mail::fake();

        $plaintext = Str::random(64);
        MagicLink::create([
            'email' => 'driver@example.com',
            'token' => MagicLink::hashToken($plaintext),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/verify', ['token' => $plaintext]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertNotNull(MagicLink::first()->used_at);
    }

    public function test_verify_rejects_expired_token(): void
    {
        $plaintext = Str::random(64);
        MagicLink::create([
            'email' => 'driver@example.com',
            'token' => MagicLink::hashToken($plaintext),
            'expires_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(20),
        ]);

        $this->postJson('/api/auth/verify', ['token' => $plaintext])
            ->assertStatus(422);
    }

    public function test_verify_rejects_already_used_token(): void
    {
        $plaintext = Str::random(64);
        MagicLink::create([
            'email' => 'driver@example.com',
            'token' => MagicLink::hashToken($plaintext),
            'expires_at' => now()->addMinutes(15),
            'used_at' => now(),
            'created_at' => now(),
        ]);

        $this->postJson('/api/auth/verify', ['token' => $plaintext])
            ->assertStatus(422);
    }

    public function test_verify_rejects_unknown_token(): void
    {
        $this->postJson('/api/auth/verify', ['token' => Str::random(64)])
            ->assertStatus(422);
    }

    public function test_me_requires_auth_and_returns_user(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);

        $user = User::factory()->create(['email' => 'me@example.com']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJson(['email' => 'me@example.com']);
    }
}
