<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;

class ResendVerificationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_verification_email_again(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->postJson('/api/email/resend', ['email' => $user->email]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Verification link sent']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
