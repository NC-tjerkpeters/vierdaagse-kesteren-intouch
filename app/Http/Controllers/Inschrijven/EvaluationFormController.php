<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationResponse;
use App\Models\Registration;
use Illuminate\Http\Request;

class EvaluationFormController extends Controller
{
    public function show(Request $request, int $evaluation, int $registration)
    {
        $evaluationModel = Evaluation::with('questions')->findOrFail($evaluation);
        $registrationModel = Registration::findOrFail($registration);

        if ($registrationModel->edition_id !== $evaluationModel->edition_id) {
            abort(404);
        }

        $existingResponse = EvaluationResponse::query()
            ->where('evaluation_id', $evaluationModel->id)
            ->where('registration_id', $registrationModel->id)
            ->first();

        if ($existingResponse) {
            return view('inschrijven.evaluatie.thank-you', [
                'evaluation' => $evaluationModel,
                'thankYouText' => $evaluationModel->thank_you_text,
            ]);
        }

        if ($evaluationModel->isClosed()) {
            return view('inschrijven.evaluatie.closed', [
                'evaluation' => $evaluationModel,
            ]);
        }

        return view('inschrijven.evaluatie.form', [
            'evaluation' => $evaluationModel,
            'registration' => $registrationModel,
        ]);
    }

    public function store(Request $request, int $evaluation, int $registration)
    {
        $evaluationModel = Evaluation::with('questions')->findOrFail($evaluation);
        $registrationModel = Registration::findOrFail($registration);

        if ($registrationModel->edition_id !== $evaluationModel->edition_id) {
            abort(404);
        }

        $existingResponse = EvaluationResponse::query()
            ->where('evaluation_id', $evaluationModel->id)
            ->where('registration_id', $registrationModel->id)
            ->first();

        if ($existingResponse) {
            return redirect()->route('inschrijven.evaluatie.form', [
                'evaluation' => $evaluation,
                'registration' => $registration,
            ])->with('info', 'Je hebt deze evaluatie al ingevuld. Bedankt!');
        }

        if ($evaluationModel->isClosed()) {
            return redirect()->route('inschrijven.evaluatie.form', [
                'evaluation' => $evaluation,
                'registration' => $registration,
            ])->with('error', 'Deze evaluatie is afgesloten.');
        }

        $rules = [];
        foreach ($evaluationModel->questions as $q) {
            $key = 'q_' . $q->id;
            if ($q->is_required) {
                $rules[$key] = ['required'];
            } else {
                $rules[$key] = ['nullable'];
            }
            if ($q->type === 'rating') {
                $rules[$key][] = 'in:1,2,3,4,5';
            }
            if ($q->type === 'nps') {
                $rules[$key][] = 'in:0,1,2,3,4,5,6,7,8,9,10';
            }
            if ($q->type === 'choice' && is_array($q->options) && ! empty($q->options)) {
                $rules[$key][] = \Illuminate\Validation\Rule::in($q->options);
            }
        }

        $data = $request->validate($rules);

        $response = EvaluationResponse::create([
            'evaluation_id' => $evaluationModel->id,
            'registration_id' => $registrationModel->id,
            'submitted_at' => now(),
        ]);

        foreach ($evaluationModel->questions as $q) {
            $value = $data['q_' . $q->id] ?? null;
            if ($value !== null && $value !== '') {
                EvaluationAnswer::create([
                    'response_id' => $response->id,
                    'question_id' => $q->id,
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                ]);
            }
        }

        return view('inschrijven.evaluatie.thank-you', [
            'evaluation' => $evaluationModel,
            'thankYouText' => $evaluationModel->thank_you_text,
        ]);
    }
}
