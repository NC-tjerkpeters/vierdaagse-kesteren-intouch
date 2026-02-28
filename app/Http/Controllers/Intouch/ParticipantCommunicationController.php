<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Distance;
use App\Models\Edition;
use App\Models\ParticipantEmailLog;
use App\Models\Registration;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ParticipantCommunicationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('communicatie_view');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $templates = config('participant_communication.templates', []);
        $distances = Distance::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);

        $query = $this->buildRecipientQuery($edition, $request);
        $recipientCount = (clone $query)->count();

        $logs = ParticipantEmailLog::query()
            ->where('edition_id', $edition->id)
            ->with('sentByUser')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('intouch.inschrijvingen.communicatie', [
            'edition' => $edition,
            'templates' => $templates,
            'distances' => $distances,
            'recipientCount' => $recipientCount,
            'logs' => $logs,
            'filters' => $request->only(['distance_id', 'status']),
        ]);
    }

    public function preview(Request $request)
    {
        $this->authorize('communicatie_view');

        $edition = Edition::current();
        if (! $edition) {
            return response()->json(['error' => 'Geen editie geselecteerd'], 400);
        }

        $templateKey = $request->input('template_key');
        $customSubject = $request->input('custom_subject');
        $customBody = $request->input('custom_body');

        $template = config("participant_communication.templates.{$templateKey}");
        if (! $template) {
            return response()->json(['error' => 'Template niet gevonden'], 400);
        }

        $registration = $this->buildRecipientQuery($edition, $request)->with('distance')->first();
        if (! $registration) {
            return response()->json([
                'error' => 'Geen deelnemers gevonden met de geselecteerde filters. Gebruik andere filters om een voorvertoning te zien.',
            ], 400);
        }

        $subject = $template['custom'] ?? false ? ($customSubject ?: '(Geen onderwerp)') : $template['subject'];
        $body = $template['custom'] ?? false ? ($customBody ?: '(Geen tekst)') : $template['body'];

        $subject = $this->replacePlaceholders($subject, $registration, $edition);
        $body = $this->replacePlaceholders($body, $registration, $edition);

        return response()->json([
            'subject' => $subject,
            'body' => $body,
            'preview_registration' => $registration->first_name . ' ' . $registration->last_name,
        ]);
    }

    public function send(Request $request)
    {
        $this->authorize('communicatie_send');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.registrations.communicatie')
                ->with('error', 'Selecteer eerst een editie.');
        }

        $templateKey = $request->input('template_key');
        $customSubject = $request->input('custom_subject');
        $customBody = $request->input('custom_body');

        $template = config("participant_communication.templates.{$templateKey}");
        if (! $template) {
            return redirect()->route('intouch.registrations.communicatie')
                ->with('error', 'Template niet gevonden.');
        }

        $registrations = $this->buildRecipientQuery($edition, $request)->with('distance')->get();
        if ($registrations->isEmpty()) {
            return redirect()->route('intouch.registrations.communicatie')
                ->with('error', 'Geen deelnemers gevonden met de geselecteerde filters.');
        }

        $subject = $template['custom'] ?? false ? $customSubject : $template['subject'];
        $body = $template['custom'] ?? false ? $customBody : $template['body'];

        if (empty($subject) || empty($body)) {
            return redirect()->route('intouch.registrations.communicatie')
                ->with('error', 'Onderwerp en bericht zijn verplicht.');
        }

        $recipientFilter = $request->only(['distance_id', 'status']);
        $log = ParticipantEmailLog::create([
            'edition_id' => $edition->id,
            'template_key' => $templateKey,
            'subject' => $subject,
            'recipient_filter' => $recipientFilter,
            'recipient_count' => $registrations->count(),
            'sent_by' => auth()->id(),
            'started_at' => now(),
        ]);

        $graphMail = app(MicrosoftGraphMailService::class);
        $sentCount = 0;
        $failedCount = 0;

        foreach ($registrations as $registration) {
            try {
                $renderedSubject = $this->replacePlaceholders($subject, $registration, $edition);
                $renderedBody = $this->replacePlaceholders($body, $registration, $edition);

                $graphMail->sendHtmlMail(
                    toAddress: $registration->email,
                    toName: trim($registration->first_name . ' ' . $registration->last_name),
                    subject: $renderedSubject,
                    htmlBody: $renderedBody,
                );
                $sentCount++;
            } catch (\Throwable $e) {
                Log::error('Participant communication: mail failed', [
                    'registration_id' => $registration->id,
                    'email' => $registration->email,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $log->update([
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'completed_at' => now(),
        ]);

        $message = "{$sentCount} e-mail(s) verstuurd.";
        if ($failedCount > 0) {
            $message .= " {$failedCount} mislukt.";
        }

        return redirect()->route('intouch.registrations.communicatie')
            ->with('status', $message);
    }

    protected function buildRecipientQuery(Edition $edition, Request $request)
    {
        $query = Registration::query()
            ->where('edition_id', $edition->id)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($request->filled('distance_id')) {
            $query->where('distance_id', $request->distance_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->where('mollie_payment_status', 'paid');
            }
            if ($request->status === 'open') {
                $query->where(function ($q) {
                    $q->whereNull('mollie_payment_status')
                        ->orWhere('mollie_payment_status', '!=', 'paid');
                });
            }
        }

        return $query;
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
