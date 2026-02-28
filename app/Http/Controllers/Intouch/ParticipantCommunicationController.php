<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Jobs\CleanupCommunicationAttachmentJob;
use App\Jobs\SendParticipantEmailJob;
use App\Models\Distance;
use App\Models\Edition;
use App\Models\ParticipantEmailLog;
use App\Models\ParticipantEmailTemplate;
use App\Models\Registration;
use Illuminate\Http\Request;

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

        $dbTemplates = ParticipantEmailTemplate::query()->orderBy('sort_order')->orderBy('name')->get();
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
            'templates' => $dbTemplates,
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

        $templateId = $request->input('template_id');
        $customSubject = $request->input('custom_subject');
        $customBody = $request->input('custom_body');

        if ($templateId === 'custom') {
            $subject = $customSubject ?: '(Geen onderwerp)';
            $body = $customBody ?: '(Geen tekst)';
        } else {
            $template = ParticipantEmailTemplate::find($templateId);
            if (! $template) {
                return response()->json(['error' => 'Template niet gevonden'], 400);
            }
            $subject = $template->subject;
            $body = $template->body;
        }

        $registration = $this->buildRecipientQuery($edition, $request)->with('distance')->first();
        if (! $registration) {
            return response()->json([
                'error' => 'Geen deelnemers gevonden met de geselecteerde filters. Gebruik andere filters om een voorvertoning te zien.',
            ], 400);
        }

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

        $templateId = $request->input('template_id');
        $customSubject = $request->input('custom_subject');
        $customBody = $request->input('custom_body');

        if ($templateId === 'custom') {
            $subject = $customSubject;
            $body = $customBody;
            $templateKey = 'custom';
        } else {
            $template = ParticipantEmailTemplate::find($templateId);
            if (! $template) {
                return redirect()->route('intouch.registrations.communicatie')
                    ->with('error', 'Template niet gevonden.');
            }
            $subject = $template->subject;
            $body = $template->body;
            $templateKey = 'tpl-' . $template->id;
        }

        $registrations = $this->buildRecipientQuery($edition, $request)->with('distance')->get();
        if ($registrations->isEmpty()) {
            return redirect()->route('intouch.registrations.communicatie')
                ->with('error', 'Geen deelnemers gevonden met de geselecteerde filters.');
        }

        if (empty($subject) || empty($body)) {
            return redirect()->route('intouch.registrations.communicatie')
                ->with('error', 'Onderwerp en bericht zijn verplicht.');
        }

        $request->validate([
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,doc,docx'], // 10 MB
        ]);

        $recipientFilter = $request->only(['distance_id', 'status']);
        $attachmentPath = null;
        $attachmentFilename = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('communicatie-attachments', 'local');
            $attachmentFilename = $file->getClientOriginalName();
        }

        $log = ParticipantEmailLog::create([
            'edition_id' => $edition->id,
            'template_key' => $templateKey,
            'subject' => $subject,
            'recipient_filter' => $recipientFilter,
            'recipient_count' => $registrations->count(),
            'sent_by' => auth()->id(),
            'started_at' => now(),
        ]);

        foreach ($registrations as $registration) {
            SendParticipantEmailJob::dispatch(
                registrationId: $registration->id,
                logId: $log->id,
                subject: $subject,
                body: $body,
                attachmentPath: $attachmentPath,
                attachmentFilename: $attachmentFilename,
            );
        }

        if ($attachmentPath) {
            CleanupCommunicationAttachmentJob::dispatch($attachmentPath)
                ->delay(now()->addMinutes(30));
        }

        $count = $registrations->count();

        return redirect()->route('intouch.registrations.communicatie')
            ->with('status', "{$count} e-mail(s) worden op de achtergrond verstuurd. Vernieuw de pagina om de voortgang in de geschiedenis te zien.");
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
