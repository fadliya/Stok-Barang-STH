<?php

namespace App\Mail;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (trim($this->apiKey) === '') {
            throw new TransportException('BREVO_API_KEY belum diisi.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $address = static fn (Address $value): array => array_filter([
            'email' => $value->getAddress(),
            'name' => $value->getName(),
        ], static fn ($value) => $value !== '');

        $payload = [
            'sender' => $address($email->getFrom()[0] ?? $message->getEnvelope()->getSender()),
            'to' => array_map($address, $email->getTo()),
            'subject' => $email->getSubject(),
        ];

        foreach (['cc' => $email->getCc(), 'bcc' => $email->getBcc()] as $field => $recipients) {
            if ($recipients) {
                $payload[$field] = array_map($address, $recipients);
            }
        }
        if ($email->getAttachments()) {
            throw new TransportException('Transport Brevo ini belum mendukung lampiran.');
        }

        if ($email->getHtmlBody() !== null) {
            $payload['htmlContent'] = $email->getHtmlBody();
        }
        if ($email->getTextBody() !== null) {
            $payload['textContent'] = $email->getTextBody();
        }
        if ($email->getReplyTo()) {
            $payload['replyTo'] = $address($email->getReplyTo()[0]);
        }

        try {
            $response = Http::withHeaders(['api-key' => $this->apiKey])
                ->acceptJson()->connectTimeout(5)->timeout(15)
                ->post('https://api.brevo.com/v3/smtp/email', $payload);
        } catch (ConnectionException) {
            throw new TransportException('Koneksi ke API Brevo gagal atau melewati batas waktu.');
        }

        if (! $response->successful()) {
            // Do not include response bodies containing message content or credentials in logs.
            throw new TransportException('API Brevo menolak pengiriman email (HTTP '.$response->status().').');
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
