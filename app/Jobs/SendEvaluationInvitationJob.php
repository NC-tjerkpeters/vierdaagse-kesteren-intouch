<?php

namespace App\Jobs;

use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\Registration;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendEvaluationInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $evaluationId,
        public int $registrationId,
    ) {}

    public function handle(MicrosoftGraphMailService $graphMail): void
    {
        $evaluation = Evaluation::with('edition', 'questions')->find($this->evaluationId);
        $registration = Registration::with('distance')->find($this->registrationId);

        if (! $evaluation || ! $registration) {
            Log::warning('SendEvaluationInvitationJob: evaluation or registration not found', [
                'evaluation_id' => $this->evaluationId,
                'registration_id' => $this->registrationId,
            ]);

            return;
        }

        if ($registration->edition_id !== $evaluation->edition_id) {
            Log::warning('SendEvaluationInvitationJob: registration does not belong to evaluation edition');

            return;
        }

        $inschrijvenUrl = 'https://' . config('app.inschrijven_domain');
        $originalUrl = config('app.url');
        config(['app.url' => $inschrijvenUrl]);

        $link = URL::temporarySignedRoute(
            'inschrijven.evaluatie.form',
            now()->addDays(30),
            ['evaluation' => $evaluation->id, 'registration' => $registration->id]
        );

        config(['app.url' => $originalUrl]);

        $subject = $this->replacePlaceholders($evaluation->mail_subject, $registration, $evaluation->edition);
        $body = $this->replacePlaceholders($evaluation->mail_body ?? '', $registration, $evaluation->edition);

        $linkHtml = '<a href="' . e($link) . '">Vul de evaluatie in</a>';
        $body = str_replace('{{link}}', '__EVAL_LINK__', $body);
        if (strpos($body, '__EVAL_LINK__') === false) {
            $body .= "\n\n__EVAL_LINK__";
        }

        $htmlBody = '<p>Beste ' . e($registration->first_name) . ',</p><p>' . nl2br(e($body)) . '</p>';
        $htmlBody = str_replace('__EVAL_LINK__', $linkHtml, $htmlBody);

        $graphMail->sendHtmlMail(
            toAddress: $registration->email,
            toName: trim($registration->first_name . ' ' . $registration->last_name),
            subject: $subject,
            htmlBody: $htmlBody,
        );
    }

    protected function replacePlaceholders(string $text, Registration $registration, Edition $edition): string
    {
        $replacements = [
            '{{voornaam}}' => $registration->first_name,
            '{{achternaam}}' => $registration->last_name,
            '{{afstand}}' => $registration->distance?->name ?? '-',
            '{{edition_name}}' => $edition->name,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
