<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Jobs\SendEvaluationInvitationJob;
use App\Jobs\SendEvaluationReminderJob;
use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvaluationController extends Controller
{
    public function index()
    {
        $this->authorize('evaluatie_view');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $evaluations = Evaluation::query()
            ->where('edition_id', $edition->id)
            ->withCount('responses')
            ->orderByDesc('created_at')
            ->get();

        $targetCounts = [];
        foreach ($evaluations as $e) {
            $targetCounts[$e->id] = $e->getTargetRegistrationsQuery()->count();
        }

        return view('intouch.inschrijvingen.evaluatie.index', [
            'edition' => $edition,
            'evaluations' => $evaluations,
            'targetCounts' => $targetCounts,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('evaluatie_send');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $initialQuestions = $request->boolean('template')
            ? config('evaluation.default_questions', [])
            : [];

        return view('intouch.inschrijvingen.evaluatie.create', [
            'edition' => $edition,
            'evaluation' => null,
            'initialQuestions' => $initialQuestions,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('evaluatie_send');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'target' => ['required', 'in:all_paid,all_finished,medal_only'],
            'intro_text' => ['nullable', 'string', 'max:2000'],
            'thank_you_text' => ['nullable', 'string', 'max:500'],
            'closes_at' => ['nullable', 'date'],
            'reminder_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'mail_subject' => ['required', 'string', 'max:255'],
            'mail_body' => ['nullable', 'string', 'max:2000'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', 'in:rating,nps,choice,text'],
            'questions.*.question_text' => ['required', 'string', 'max:500'],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.options' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        $evaluation = Evaluation::create([
            'edition_id' => $edition->id,
            'name' => $data['name'],
            'target' => $data['target'],
            'intro_text' => $data['intro_text'] ?? null,
            'thank_you_text' => $data['thank_you_text'] ?? 'Bedankt voor je feedback!',
            'closes_at' => $data['closes_at'] ?? null,
            'reminder_days' => ! empty($data['reminder_days']) ? (int) $data['reminder_days'] : null,
            'mail_subject' => $data['mail_subject'],
            'mail_body' => $data['mail_body'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->syncQuestions($evaluation, $data['questions']);

        return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
            ->with('status', 'Evaluatie opgeslagen. Je kunt hem nu versturen.');
    }

    public function show(Evaluation $evaluation)
    {
        $this->authorize('evaluatie_view');
        $this->ensureEdition($evaluation);

        $evaluation->load('questions');
        $targetCount = $evaluation->getTargetRegistrationsQuery()->count();

        return view('intouch.inschrijvingen.evaluatie.show', [
            'evaluation' => $evaluation,
            'targetCount' => $targetCount,
        ]);
    }

    public function edit(Evaluation $evaluation)
    {
        $this->authorize('evaluatie_manage');
        $this->ensureEdition($evaluation);

        if ($evaluation->isSent()) {
            return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
                ->with('error', 'Een verstuurde evaluatie kan niet meer worden bewerkt.');
        }

        return view('intouch.inschrijvingen.evaluatie.edit', [
            'evaluation' => $evaluation->load('questions'),
        ]);
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $this->authorize('evaluatie_manage');
        $this->ensureEdition($evaluation);

        if ($evaluation->isSent()) {
            return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
                ->with('error', 'Een verstuurde evaluatie kan niet meer worden bewerkt.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'target' => ['required', 'in:all_paid,all_finished,medal_only'],
            'intro_text' => ['nullable', 'string', 'max:2000'],
            'thank_you_text' => ['nullable', 'string', 'max:500'],
            'closes_at' => ['nullable', 'date'],
            'reminder_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'mail_subject' => ['required', 'string', 'max:255'],
            'mail_body' => ['nullable', 'string', 'max:2000'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', 'in:rating,nps,choice,text'],
            'questions.*.question_text' => ['required', 'string', 'max:500'],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.options' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        $evaluation->update([
            'name' => $data['name'],
            'target' => $data['target'],
            'intro_text' => $data['intro_text'] ?? null,
            'thank_you_text' => $data['thank_you_text'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'reminder_days' => ! empty($data['reminder_days']) ? (int) $data['reminder_days'] : null,
            'mail_subject' => $data['mail_subject'],
            'mail_body' => $data['mail_body'] ?? null,
        ]);

        $this->syncQuestions($evaluation, $data['questions']);

        return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
            ->with('status', 'Evaluatie bijgewerkt.');
    }

    public function send(Evaluation $evaluation)
    {
        $this->authorize('evaluatie_send');
        $this->ensureEdition($evaluation);

        if ($evaluation->isSent()) {
            return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
                ->with('error', 'Deze evaluatie is al verstuurd.');
        }

        if ($evaluation->questions()->count() === 0) {
            return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
                ->with('error', 'Voeg minimaal één vraag toe voor je verstuurt.');
        }

        $registrations = $evaluation->getTargetRegistrationsQuery()->get();
        if ($registrations->isEmpty()) {
            return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
                ->with('error', 'Geen deelnemers in de doelgroep. Pas de doelgroep aan of wacht tot er inschrijvingen zijn.');
        }

        $total = $registrations->count();

        try {
            $evaluation->update([
                'sent_at' => now(),
                'invitations_sent_count' => 0,
                'invitations_total' => $total,
            ]);

            foreach ($registrations as $registration) {
                SendEvaluationInvitationJob::dispatch($evaluation->id, $registration->id);
            }

            if ($evaluation->reminder_days > 0) {
                SendEvaluationReminderJob::dispatch($evaluation->id)
                    ->delay(now()->addDays($evaluation->reminder_days));
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
                ->with('error', 'Uitnodigingen konden niet in de wachtrij worden gezet. Controleer of de queue-worker draait en probeer het opnieuw.');
        }

        return redirect()->route('intouch.registrations.evaluatie.show', $evaluation)
            ->with('status', "{$total} uitnodiging(en) worden op de achtergrond verstuurd.");
    }

    public function sendStatus(Evaluation $evaluation)
    {
        $this->authorize('evaluatie_view');
        $this->ensureEdition($evaluation);

        $total = $evaluation->invitations_total ?? 0;
        $sent = $evaluation->invitations_sent_count ?? 0;

        return response()->json([
            'sent' => (int) $sent,
            'total' => (int) $total,
        ]);
    }

    public function results(Evaluation $evaluation)
    {
        $this->authorize('evaluatie_view');
        $this->ensureEdition($evaluation);

        $evaluation->load('questions.answers');
        $responses = $evaluation->responses()->with('registration')->get();

        $aggregates = [];
        foreach ($evaluation->questions as $q) {
            $values = $q->answers->pluck('value')->filter()->values();
            $aggregates[$q->id] = $this->aggregateQuestion($q, $values);
        }

        return view('intouch.inschrijvingen.evaluatie.results', [
            'evaluation' => $evaluation,
            'responses' => $responses,
            'aggregates' => $aggregates,
        ]);
    }

    public function export(Evaluation $evaluation): StreamedResponse
    {
        $this->authorize('evaluatie_view');
        $this->ensureEdition($evaluation);

        $evaluation->load(['questions', 'responses.registration.distance', 'responses.answers']);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="evaluatie-' . \Str::slug($evaluation->name) . '-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->streamDownload(function () use ($evaluation) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['UTF-8'], ';');
            $headerRow = ['Naam', 'E-mail', 'Afstand', 'Ingevuld op'];
            foreach ($evaluation->questions as $q) {
                $headerRow[] = $q->question_text;
            }
            fputcsv($handle, $headerRow, ';');

            foreach ($evaluation->responses as $response) {
                $row = [
                    $response->registration->first_name . ' ' . $response->registration->last_name,
                    $response->registration->email,
                    $response->registration->distance?->name ?? '-',
                    $response->submitted_at->format('d-m-Y H:i'),
                ];
                $answerMap = $response->answers->keyBy('question_id');
                foreach ($evaluation->questions as $q) {
                    $row[] = $answerMap->get($q->id)?->value ?? '';
                }
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, 'evaluatie.csv', $headers);
    }

    public function destroy(Evaluation $evaluation)
    {
        $this->authorize('evaluatie_manage');
        $this->ensureEdition($evaluation);

        if ($evaluation->isSent() && $evaluation->responses()->count() > 0) {
            return redirect()->route('intouch.registrations.evaluatie.index')
                ->with('error', 'Evaluaties met reacties kunnen niet worden verwijderd.');
        }

        $evaluation->delete();

        return redirect()->route('intouch.registrations.evaluatie.index')
            ->with('status', 'Evaluatie verwijderd.');
    }

    protected function ensureEdition(Evaluation $evaluation): void
    {
        $edition = Edition::current();
        if (! $edition || $evaluation->edition_id !== $edition->id) {
            abort(404);
        }
    }

    protected function syncQuestions(Evaluation $evaluation, array $questions): void
    {
        $evaluation->questions()->delete();

        foreach ($questions as $i => $qData) {
            $options = null;
            if (($qData['type'] ?? '') === 'choice' && ! empty(trim($qData['options'] ?? ''))) {
                $opts = array_map('trim', explode("\n", $qData['options']));
                $options = array_values(array_filter($opts));
            }

            $evaluation->questions()->create([
                'type' => $qData['type'],
                'question_text' => $qData['question_text'],
                'sort_order' => $i,
                'options' => $options,
                'is_required' => filter_var($qData['is_required'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    protected function aggregateQuestion(EvaluationQuestion $q, $values): array
    {
        if ($values->isEmpty()) {
            return ['type' => $q->type, 'count' => 0];
        }

        $numeric = $values->map(fn ($v) => is_numeric($v) ? (float) $v : null)->filter();

        if ($q->type === 'nps' && $numeric->isNotEmpty()) {
            $avg = round($numeric->avg(), 1);
            $promoters = $numeric->filter(fn ($v) => $v >= 9)->count();
            $passives = $numeric->filter(fn ($v) => $v >= 7 && $v < 9)->count();
            $detractors = $numeric->filter(fn ($v) => $v < 7)->count();

            $distribution = [];
            foreach (range(0, 10) as $i) {
                $distribution[$i] = $numeric->filter(fn ($v) => (int) $v === $i)->count();
            }

            return [
                'type' => 'nps',
                'count' => $values->count(),
                'average' => $avg,
                'distribution' => $distribution,
                'promoters' => $promoters,
                'passives' => $passives,
                'detractors' => $detractors,
            ];
        }

        if ($q->type === 'rating' && $numeric->isNotEmpty()) {
            $dist = [];
            foreach (range(1, 5) as $i) {
                $dist[$i] = $values->filter(fn ($v) => (int) $v === $i)->count();
            }

            return [
                'type' => 'rating',
                'count' => $values->count(),
                'average' => round($numeric->avg(), 1),
                'distribution' => $dist,
            ];
        }

        if ($q->type === 'choice') {
            $counts = $values->countBy();

            return [
                'type' => 'choice',
                'count' => $values->count(),
                'counts' => $counts->all(),
            ];
        }

        return [
            'type' => 'text',
            'count' => $values->count(),
            'values' => $values->all(),
        ];
    }
}
