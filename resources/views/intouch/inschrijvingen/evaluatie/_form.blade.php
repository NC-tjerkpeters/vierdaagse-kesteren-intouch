<form method="post" action="{{ $action }}" id="evaluation-form">
    @csrf
    @if($method === 'put') @method('put') @endif

    <div class="card mb-4">
        <div class="card-header">Basisgegevens</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Naam *</label>
                <input type="text" id="name" name="name" class="form-control" required
                    value="{{ old('name', $evaluation?->name) }}" placeholder="Evaluatie Vierdaagse {{ $edition->name }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Doelgroep *</label>
                <div>
                    <div class="form-check">
                        <input type="radio" name="target" id="target_all" value="all_paid" class="form-check-input"
                            @checked(old('target', $evaluation?->target ?? 'all_paid') === 'all_paid')>
                        <label class="form-check-label" for="target_all">Alle betaalde deelnemers</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="target" id="target_finished" value="all_finished" class="form-check-input"
                            @checked(old('target', $evaluation?->target) === 'all_finished')>
                        <label class="form-check-label" for="target_finished">Alleen deelnemers die alle 4 avonden hebben voltooid</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="target" id="target_medal" value="medal_only" class="form-check-input"
                            @checked(old('target', $evaluation?->target) === 'medal_only')>
                        <label class="form-check-label" for="target_medal">Alleen deelnemers die een medaille willen</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="closes_at" class="form-label">Sluitingsdatum (optioneel)</label>
                <input type="datetime-local" id="closes_at" name="closes_at" class="form-control"
                    value="{{ old('closes_at', $evaluation?->closes_at?->format('Y-m-d\TH:i')) }}">
                <small class="text-muted">Na deze datum kan het formulier niet meer worden ingevuld.</small>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Vragen</div>
        <div class="card-body">
            <div id="questions-container">
                @php
                    $questions = old('questions', $evaluation?->questions ?? ($initialQuestions ?? []));
                    if (empty($questions)) {
                        $questions = [['type' => 'nps', 'question_text' => '', 'is_required' => true, 'options' => []]];
                    }
                @endphp
                @foreach($questions as $i => $q)
                <div class="question-block mb-4 p-3 border rounded" data-index="{{ $i }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>Vraag {{ $i + 1 }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question">Verwijder</button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Vraagtekst *</label>
                        <input type="text" name="questions[{{ $i }}][question_text]" class="form-control" required
                            value="{{ is_array($q) ? ($q['question_text'] ?? '') : $q->question_text }}">
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label small">Type</label>
                            <select name="questions[{{ $i }}][type]" class="form-select form-select-sm question-type">
                                @foreach(\App\Models\EvaluationQuestion::typeLabels() as $k => $v)
                                <option value="{{ $k }}" @selected((is_array($q) ? ($q['type'] ?? '') : $q->type) === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="hidden" name="questions[{{ $i }}][is_required]" value="0">
                                <input type="checkbox" name="questions[{{ $i }}][is_required]" value="1" class="form-check-input"
                                    @checked(is_array($q) ? ($q['is_required'] ?? true) : $q->is_required)>
                                <label class="form-check-label small">Verplicht</label>
                            </div>
                        </div>
                    </div>
                    <div class="options-field mb-0" style="{{ (is_array($q) ? ($q['type'] ?? '') : $q->type) === 'choice' ? '' : 'display:none' }}">
                        <label class="form-label small">Opties (één per regel)</label>
                        <textarea name="questions[{{ $i }}][options]" class="form-control form-control-sm" rows="2">{{ is_array($q) ? implode("\n", $q['options'] ?? []) : implode("\n", $q->options ?? []) }}</textarea>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" id="add-question" class="btn btn-outline-primary">+ Vraag toevoegen</button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Teksten</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="intro_text" class="form-label">Introtekst (boven het formulier)</label>
                <textarea id="intro_text" name="intro_text" class="form-control" rows="3">{{ old('intro_text', $evaluation?->intro_text ?? 'Bedankt voor je deelname aan de Vierdaagse Kesteren! We horen graag je mening om het evenement volgend jaar nog beter te maken. Het duurt ongeveer 2 minuten.') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="thank_you_text" class="form-label">Bedanktekst (na versturen)</label>
                <input type="text" id="thank_you_text" name="thank_you_text" class="form-control"
                    value="{{ old('thank_you_text', $evaluation?->thank_you_text ?? 'Bedankt voor je feedback! We nemen je suggesties mee.') }}">
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">E-mail bij versturen</div>
        <div class="card-body">
            @php
                $linkPlaceholder = '{{link}}';
                $mailBodyDefault = "Bedankt voor je deelname! Vul onderstaande enquête in:\n\n" . $linkPlaceholder;
            @endphp
            <div class="mb-3">
                <label for="mail_subject" class="form-label">Mailonderwerp *</label>
                <input type="text" id="mail_subject" name="mail_subject" class="form-control" required
                    value="{{ old('mail_subject', $evaluation?->mail_subject ?? 'Jouw mening over de Vierdaagse Kesteren ' . $edition->name) }}">
            </div>
            <div class="mb-3">
                <label for="mail_body" class="form-label">Mailtekst (gebruik {{ $linkPlaceholder }} voor de persoonlijke link)</label>
                <textarea id="mail_body" name="mail_body" class="form-control" rows="4">{{ old('mail_body', $evaluation?->mail_body ?? $mailBodyDefault) }}</textarea>
            </div>
            <div class="mb-3">
                <label for="reminder_days" class="form-label">Herinnering (dagen na versturen)</label>
                <input type="number" id="reminder_days" name="reminder_days" class="form-control" min="0" max="90" step="1"
                    value="{{ old('reminder_days', $evaluation?->reminder_days ?? '') }}" placeholder="0 = geen herinnering">
                <small class="text-muted">Stuur na dit aantal dagen een herinnering naar deelnemers die nog niet hebben gereageerd. Laat leeg of 0 voor geen herinnering.</small>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
    <a href="{{ route('intouch.registrations.evaluatie.index') }}" class="btn btn-outline-secondary">Annuleren</a>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionIndex = {{ count($questions) }};

    document.getElementById('add-question').addEventListener('click', function() {
        const container = document.getElementById('questions-container');
        const block = document.createElement('div');
        block.className = 'question-block mb-4 p-3 border rounded';
        block.dataset.index = questionIndex;
        block.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <strong>Vraag ` + (questionIndex + 1) + `</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">Verwijder</button>
            </div>
            <div class="mb-2">
                <label class="form-label small">Vraagtekst *</label>
                <input type="text" name="questions[${questionIndex}][question_text]" class="form-control" required>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label small">Type</label>
                    <select name="questions[${questionIndex}][type]" class="form-select form-select-sm question-type">
                        <option value="rating">Beoordeling 1–5</option>
                        <option value="nps">NPS (0–10)</option>
                        <option value="choice">Meerkeuze (enkel)</option>
                        <option value="text">Open vraag</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input type="hidden" name="questions[${questionIndex}][is_required]" value="0">
                        <input type="checkbox" name="questions[${questionIndex}][is_required]" value="1" class="form-check-input" checked>
                        <label class="form-check-label small">Verplicht</label>
                    </div>
                </div>
            </div>
            <div class="options-field mb-0" style="display:none">
                <label class="form-label small">Opties (één per regel)</label>
                <textarea name="questions[${questionIndex}][options]" class="form-control form-control-sm" rows="2"></textarea>
            </div>
        `;
        container.appendChild(block);
        block.querySelector('.question-type').addEventListener('change', toggleOptions);
        block.querySelector('.remove-question').addEventListener('click', () => block.remove());
        questionIndex++;
    });

    document.getElementById('questions-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-question')) {
            e.target.closest('.question-block').remove();
        }
    });

    document.querySelectorAll('.question-type').forEach(el => {
        el.addEventListener('change', toggleOptions);
    });

    function toggleOptions(e) {
        const block = e.target.closest('.question-block');
        const optionsField = block.querySelector('.options-field');
        optionsField.style.display = e.target.value === 'choice' ? '' : 'none';
    }
});
</script>
@endpush
