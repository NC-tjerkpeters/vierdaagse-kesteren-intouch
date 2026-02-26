<?php

namespace App\Services;

use App\Models\Sponsor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SponsorReceiptService
{
    public function __construct(
        protected MicrosoftGraphMailService $graphMail
    ) {}

    public function sendReceipt(Sponsor $sponsor): void
    {
        Log::info('Sponsor receipt mail: start', [
            'sponsor_id' => $sponsor->id,
            'to' => $sponsor->email,
        ]);

        $accessToken = $this->graphMail->getAccessToken();
        $yearMonth = date('ym');
        $filename = $yearMonth . '-' . $sponsor->invoice_id;
        $bedrag = number_format((float) $sponsor->bedrag, 2, ',', '.');
        $datum = date('d-m-Y');

        $topBannerPath = public_path('images/pdf/top-banner.png');
        $topBannerBase64 = file_exists($topBannerPath) ? base64_encode(file_get_contents($topBannerPath)) : '';

        $pdf = Pdf::loadView('pdf.sponsor_receipt', [
            'sponsor' => $sponsor,
            'bedrag' => $bedrag,
            'datum' => $datum,
            'filename' => $filename,
            'topBannerBase64' => $topBannerBase64,
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();

        $htmlBody = $this->buildEmailBody($sponsor);

        $this->sendMailViaGraph(
            accessToken: $accessToken,
            toAddress: $sponsor->email,
            toName: trim($sponsor->voornaam . ' ' . $sponsor->achternaam),
            subject: 'Kwitantie ' . $filename . ' ' . $sponsor->bedrijfsnaam,
            htmlBody: $htmlBody,
            pdfContent: $pdfContent,
            filename: $filename . '.pdf',
        );

        Log::info('Sponsor receipt mail: sent', ['sponsor_id' => $sponsor->id]);
    }

    protected function buildEmailBody(Sponsor $sponsor): string
    {
        $year = date('Y');
        $voornaam = e($sponsor->voornaam);

        return "Beste {$voornaam},<br><br>
        Super leuk dat je dit jaar onze vriend wilt zijn bij de vierdaagse van Kesteren!<br><br>
        We gaan er met z'n allen weer een mooie editie van maken.<br><br>
        In de bijlage de kwitantie voor sponsoring van Stichting De Hoenderik - Vierdaagse Kesteren {$year}.<br><br>
        Met vriendelijke groet,<br><br>
        Namens het vierdaagse Team";
    }

    protected function sendMailViaGraph(
        string $accessToken,
        string $toAddress,
        string $toName,
        string $subject,
        string $htmlBody,
        string $pdfContent,
        string $filename = 'kwitantie.pdf',
    ): void {
        $sender = config('services.msgraph.sender_address');
        $bcc = config('sponsors.receipt_bcc', 'mail@vierdaagsekesteren.nl');

        $toRecipients = [
            [
                'emailAddress' => [
                    'address' => $toAddress,
                    'name' => $toName,
                ],
            ],
        ];

        $bccRecipients = [];
        if ($bcc) {
            $bccRecipients[] = [
                'emailAddress' => [
                    'address' => $bcc,
                    'name' => 'Vierdaagse Kesteren',
                ],
            ];
        }

        $message = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $htmlBody,
            ],
            'toRecipients' => $toRecipients,
            'from' => [
                'emailAddress' => [
                    'address' => $sender,
                ],
            ],
            'attachments' => [
                [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $filename,
                    'contentType' => 'application/pdf',
                    'contentBytes' => base64_encode($pdfContent),
                ],
            ],
        ];

        if (! empty($bccRecipients)) {
            $message['bccRecipients'] = $bccRecipients;
        }

        $payload = [
            'message' => $message,
            'saveToSentItems' => true,
        ];

        $endpoint = "https://graph.microsoft.com/v1.0/users/{$sender}/sendMail";

        $response = Http::withToken($accessToken)->post($endpoint, $payload);

        if (! $response->successful()) {
            Log::error('Microsoft Graph: sponsor receipt send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Verzenden kwitantie mislukt: ' . $response->body());
        }
    }
}
