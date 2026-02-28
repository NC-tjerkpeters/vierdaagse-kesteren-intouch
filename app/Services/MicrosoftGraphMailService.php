<?php

namespace App\Services;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class MicrosoftGraphMailService
{
    public function sendRegistrationTicket(Registration $registration): void
    {
        Log::info('Registration ticket mail: start', [
            'registration_id' => $registration->id,
            'to' => $registration->email,
        ]);

        try {
            $accessToken = $this->getAccessToken();
            Log::info('Registration ticket mail: access token obtained');

            $qrImageBase64 = $this->getQrImageAsBase64($registration->qr_code);
            Log::info('Registration ticket mail: QR image', [
                'qr_fetched' => strlen($qrImageBase64) > 0,
            ]);

            $topBannerBase64 = $this->getPdfImageAsBase64('top-banner.png');
            $birdBase64 = $this->getPdfImageAsBase64('bird.png');

            $htmlBody = View::make('emails.registration_ticket', [
                'registration' => $registration,
                'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($registration->qr_code),
            ])->render();

            $pdf = Pdf::loadView('pdf.registration_ticket', [
                'registration' => $registration,
                'qrImageBase64' => $qrImageBase64,
                'topBannerBase64' => $topBannerBase64,
                'birdBase64' => $birdBase64,
            ])->setPaper('a4', 'portrait');

            $pdfContent = $pdf->output();
            Log::info('Registration ticket mail: PDF generated', ['pdf_size' => strlen($pdfContent)]);

            $this->sendMailViaGraph(
                accessToken: $accessToken,
                toAddress: $registration->email,
                toName: trim($registration->first_name . ' ' . $registration->last_name),
                subject: 'Ticket Vierdaagse Kesteren',
                htmlBody: $htmlBody,
                pdfContent: $pdfContent,
            );

            Log::info('Registration ticket mail: sent successfully', ['registration_id' => $registration->id]);
        } catch (\Throwable $e) {
            Log::error('Microsoft Graph mail sending failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function getPdfImageAsBase64(string $filename): string
    {
        $path = public_path('images/pdf/' . $filename);
        if (! file_exists($path)) {
            return '';
        }
        return base64_encode(file_get_contents($path));
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

    public function getAccessToken(): string
    {
        $tenantId = config('services.msgraph.tenant_id');
        $clientId = config('services.msgraph.client_id');
        $clientSecret = config('services.msgraph.client_secret');

        $response = Http::asForm()->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
            'client_id' => $clientId,
            'scope' => 'https://graph.microsoft.com/.default',
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            Log::error('Microsoft Graph: token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Could not obtain access token from Microsoft Graph: ' . $response->body());
        }

        return $response->json('access_token');
    }

    protected function sendMailViaGraph(
        string $accessToken,
        string $toAddress,
        string $toName,
        string $subject,
        string $htmlBody,
        string $pdfContent,
    ): void {
        $sender = config('services.msgraph.sender_address');

        $endpoint = "https://graph.microsoft.com/v1.0/users/{$sender}/sendMail";

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $htmlBody,
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $toAddress,
                            'name' => $toName,
                        ],
                    ],
                ],
                'from' => [
                    'emailAddress' => [
                        'address' => $sender,
                    ],
                ],
                'attachments' => [
                    [
                        '@odata.type' => '#microsoft.graph.fileAttachment',
                        'name' => 'ticket-vierdaagse-kesteren.pdf',
                        'contentType' => 'application/pdf',
                        'contentBytes' => base64_encode($pdfContent),
                    ],
                ],
            ],
            'saveToSentItems' => true,
        ];

        $response = Http::withToken($accessToken)
            ->post($endpoint, $payload);

        if (! $response->successful()) {
            Log::error('Microsoft Graph: sendMail failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Sending mail via Microsoft Graph failed: ' . $response->body());
        }
    }

    public function sendHtmlMail(
        string $toAddress,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $attachmentPath = null,
        ?string $attachmentFilename = null,
    ): void {
        $accessToken = $this->getAccessToken();
        $sender = config('services.msgraph.sender_address');
        $endpoint = "https://graph.microsoft.com/v1.0/users/{$sender}/sendMail";

        $message = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $htmlBody,
            ],
            'toRecipients' => [
                [
                    'emailAddress' => [
                        'address' => $toAddress,
                        'name' => $toName,
                    ],
                ],
            ],
            'from' => [
                'emailAddress' => [
                    'address' => $sender,
                ],
            ],
        ];

        if ($attachmentPath && \Illuminate\Support\Facades\Storage::disk('local')->exists($attachmentPath)) {
            $content = \Illuminate\Support\Facades\Storage::disk('local')->get($attachmentPath);
            $filename = $attachmentFilename ?? basename($attachmentPath);
            $mimeType = \Illuminate\Support\Facades\Storage::disk('local')->mimeType($attachmentPath) ?: 'application/octet-stream';
            $message['attachments'] = [
                [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $filename,
                    'contentType' => $mimeType,
                    'contentBytes' => base64_encode($content),
                ],
            ];
        }

        $payload = [
            'message' => $message,
            'saveToSentItems' => true,
        ];

        $response = Http::withToken($accessToken)->post($endpoint, $payload);

        if (! $response->successful()) {
            Log::error('Microsoft Graph: sendHtmlMail failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Verzenden mail mislukt: ' . $response->body());
        }
    }

    public function sendPasswordResetMail(\App\Models\User $user, string $resetUrl): void
    {
        $expireMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);
        $htmlBody = 'Beste ' . e($user->name) . ',<br><br>' .
            'Je ontvangt deze e-mail omdat we een wachtwoordherstelverzoek hebben ontvangen voor je account.<br><br>' .
            '<a href="' . e($resetUrl) . '" style="display:inline-block;padding:12px 24px;background:#2e7d32;color:#fff;text-decoration:none;border-radius:6px;">Wachtwoord herstellen</a><br><br>' .
            'Deze link verloopt over ' . $expireMinutes . ' minuten.<br><br>' .
            'Als je geen wachtwoordherstel hebt aangevraagd, kun je deze e-mail negeren.';

        $this->sendHtmlMail(
            toAddress: $user->getEmailForPasswordReset(),
            toName: $user->name,
            subject: 'Wachtwoord herstellen – Intouch Vierdaagse Kesteren',
            htmlBody: $htmlBody,
        );
    }
}

