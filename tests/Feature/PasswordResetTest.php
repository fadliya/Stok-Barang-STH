<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['username' => 'reset-user', 'password' => Hash::make('old-password')]);
    }

    public function test_guest_can_request_a_reset_link_without_disclosing_registered_emails(): void
    {
        Notification::fake();
        $user = $this->user();
        $this->get('/login')->assertOk()->assertSee('Lupa password?');
        $this->get('/forgot-password')->assertOk();
        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])->assertRedirect('/forgot-password')->assertSessionHas('status');
        $status = session('status');
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->assertTrue(Password::tokenExists($user, $notification->token));
            $url = $notification->toMail($user)->actionUrl;
            $this->assertStringStartsWith(config('app.url'), $url);
            $this->get($url)->assertOk()->assertSee('Buat Password Baru');

            return true;
        });
        $this->post('/forgot-password', ['email' => 'unknown@example.com'])->assertSessionHas('status', $status);
        Notification::assertCount(1);
    }

    public function test_valid_token_changes_password_and_cannot_be_reused(): void
    {
        $user = $this->user();
        $rememberToken = $user->remember_token;
        $token = Password::createToken($user);
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'];
        $this->post('/reset-password', $data)->assertRedirect('/login')->assertSessionHas('info');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertNotSame($rememberToken, $user->fresh()->remember_token);
        $this->assertFalse(Password::tokenExists($user, $token));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
        $this->post('/login', ['username' => $user->username, 'password' => 'new-password'])->assertRedirect('/dashboard');
    }

    public function test_invalid_and_expired_tokens_do_not_change_password(): void
    {
        $user = $this->user();
        $data = ['email' => $user->email, 'token' => 'invalid-token', 'password' => 'new-password', 'password_confirmation' => 'new-password'];
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
        $data['token'] = Password::createToken($user);
        $this->travel(61)->minutes();
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_password_confirmation_and_email_are_validated(): void
    {
        $this->post('/forgot-password', ['email' => 'invalid'])->assertSessionHasErrors('email');
        $user = $this->user();
        $this->post('/reset-password', [
            'email' => $user->email, 'token' => Password::createToken($user),
            'password' => 'short', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_reset_requests_are_rate_limited(): void
    {
        Notification::fake();
        $user = $this->user();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();
        }
        Notification::assertCount(1);
        $this->post('/forgot-password', ['email' => $user->email])->assertStatus(429);
    }
}
