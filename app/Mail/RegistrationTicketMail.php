<?php

namespace App\Mail;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class RegistrationTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket Vierdaagse Kesteren',
        );
    }

    public function content(): Content
    {
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($this->registration->qr_code);

        return new Content(
            view: 'emails.registration_ticket',
            with: [
                'registration' => $this->registration,
                'qrUrl' => $qrUrl,
            ],
        );
    }

    public function attachments(): array
    {
        $qrImageBase64 = $this->getQrImageAsBase64($this->registration->qr_code);
        $topBannerBase64 = $this->getPdfImageAsBase64('top-banner.png');
        $birdBase64 = $this->getPdfImageAsBase64('bird.png');

        $pdf = Pdf::loadView('pdf.registration_ticket', [
            'registration' => $this->registration,
            'qrImageBase64' => $qrImageBase64,
            'topBannerBase64' => $topBannerBase64,
            'birdBase64' => $birdBase64,
        ])->setPaper('a4', 'portrait');

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), 'ticket-vierdaagse-kesteren.pdf')
                ->withMime('application/pdf'),
        ];
    }

    protected function getQrImageAsBase64(string $qrData): string
    {
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($qrData);
        $response = Http::get($url);
        if (! $response->successful()) {
            return '';
        }
        return base64_encode($response->body());
    }

    protected function getPdfImageAsBase64(string $filename): string
    {
        $path = public_path('images/pdf/' . $filename);
        if (! file_exists($path)) {
            return '';
        }
        return base64_encode(file_get_contents($path));
    }
}
