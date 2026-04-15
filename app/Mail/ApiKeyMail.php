<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApiKeyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $requesterName,
        public string $plainApiKey
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sua Chave de API - SIMAH',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.api-key',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
