<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class BrevoMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'mail.default' => 'brevo',
            'services.brevo.key' => 'test-api-key',
            'mail.from.address' => 'sender@example.com',
            'mail.from.name' => 'STH NETWORK',
        ]);
        Http::preventStrayRequests();
    }

    public function test_password_reset_sends_link_through_brevo_https_api(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => 'test-id'], 201)]);
        $user = User::factory()->create(['username' => 'brevo-user']);

        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.brevo.com/v3/smtp/email'
            && $request->hasHeader('api-key', 'test-api-key')
            && $request['sender']['email'] === 'sender@example.com'
            && $request['to'][0]['email'] === $user->email
            && str_contains($request['htmlContent'], '/reset-password/')
            && str_contains($request['textContent'], '/reset-password/'));
        Http::assertSentCount(1);
    }

    public function test_api_rejection_does_not_expose_response_body(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['message' => 'sensitive-content'], 401)]);
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('API Brevo menolak pengiriman email (HTTP 401).');
        Mail::raw('Test', fn ($message) => $message->to('recipient@example.com')->subject('Test'));
    }

    public function test_missing_key_fails_without_http_request(): void
    {
        config(['services.brevo.key' => '']);
        try {
            Mail::raw('Test', fn ($message) => $message->to('recipient@example.com')->subject('Test'));
            $this->fail('Missing key must fail.');
        } catch (TransportException $exception) {
            $this->assertSame('BREVO_API_KEY belum diisi.', $exception->getMessage());
        }
        Http::assertNothingSent();
    }
}
