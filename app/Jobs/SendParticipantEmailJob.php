<?php

namespace App\Jobs;

use App\Models\Edition;
use App\Models\ParticipantEmailLog;
use App\Models\Registration;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendParticipantEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public int $registrationId,
        public int $logId,
        public string $subject,
        public string $body,
        public ?string $attachmentPath,
        public ?string $attachmentFilename,
    ) {}

    public function handle(MicrosoftGraphMailService $graphMail): void
    {
        $registration = Registration::with('distance')->find($this->registrationId);
        if (! $registration) {
            Log::warning('SendParticipantEmailJob: registration not found', ['id' => $this->registrationId]);
            $this->incrementLogFailed();

            return;
        }

        $edition = Edition::find($registration->edition_id);
        if (! $edition) {
            Log::warning('SendParticipantEmailJob: edition not found', ['id' => $registration->edition_id]);
            $this->incrementLogFailed();

            return;
        }

        $subject = $this->replacePlaceholders($this->subject, $registration, $edition);
        $body = $this->replacePlaceholders($this->body, $registration, $edition);

        $graphMail->sendHtmlMail(
            toAddress: $registration->email,
            toName: trim($registration->first_name . ' ' . $registration->last_name),
            subject: $subject,
            htmlBody: $body,
            attachmentPath: $this->attachmentPath,
            attachmentFilename: $this->attachmentFilename,
        );

        $this->incrementLogSent();
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Participant communication: mail failed after retries', [
            'registration_id' => $this->registrationId,
            'error' => $e->getMessage(),
        ]);
        $this->incrementLogFailed();
    }

    protected function incrementLogSent(): void
    {
        $log = ParticipantEmailLog::find($this->logId);
        if ($log) {
            $log->increment('sent_count');
            $this->maybeCompleteLog($log);
        }
    }

    protected function incrementLogFailed(): void
    {
        $log = ParticipantEmailLog::find($this->logId);
        if ($log) {
            $log->increment('failed_count');
            $this->maybeCompleteLog($log);
        }
    }

    protected function maybeCompleteLog(ParticipantEmailLog $log): void
    {
        $log->refresh();
        if ($log->sent_count + $log->failed_count >= $log->recipient_count) {
            $log->update(['completed_at' => now()]);
        }
    }

    protected function replacePlaceholders(string $text, Registration $registration, Edition $edition): string
    {
        $inschrijfUrl = 'https://' . config('app.inschrijven_domain');
        $routesUrl = 'https://' . config('app.routes_domain');

        $replacements = [
            '{{voornaam}}' => $registration->first_name,
            '{{achternaam}}' => $registration->last_name,
            '{{afstand}}' => $registration->distance?->name ?? '-',
            '{{edition_name}}' => $edition->name,
            '{{start_datum}}' => $edition->start_date?->format('d-m-Y') ?? '-',
            '{{eind_datum}}' => $edition->end_date?->format('d-m-Y') ?? '-',
            '{{inschrijf_url}}' => $inschrijfUrl,
            '{{routes_url}}' => $routesUrl,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
