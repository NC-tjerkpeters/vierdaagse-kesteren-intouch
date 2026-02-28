@extends('intouch.layout')

@section('title', 'Communicatie')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Communicatie naar deelnemers</h1>
    <div>
        @can('communicatie_templates')
        <a href="{{ route('intouch.registrations.communicatie.templates') }}" class="btn btn-outline-secondary me-1">Templates beheren</a>
        @endcan
        <a href="{{ route('intouch.registrations.index') }}" class="btn btn-outline-secondary">← Inschrijvingen</a>
    </div>
</div>

<p class="text-muted mb-4">
    Stuur e-mails naar deelnemers van {{ $edition->name }}. Filter op afstand of betaalstatus om de juiste groep te bereiken.
</p>

<form method="get" action="{{ route('intouch.registrations.communicatie') }}" id="filter-form" class="card mb-4">
    <div class="card-header">Doelgroep</div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="distance_id" class="form-label">Afstand</label>
                <select id="distance_id" name="distance_id" class="form-select">
                    <option value="">Alle afstanden</option>
                    @foreach($distances as $d)
                        <option value="{{ $d->id }}" @selected(($filters['distance_id'] ?? '') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Betaling</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Alle</option>
                    <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Betaald</option>
                    <option value="open" @selected(($filters['status'] ?? '') === 'open')>Nog niet betaald</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter toepassen</button>
            </div>
            <div class="col-md-3 text-end">
                <strong>{{ $recipientCount }}</strong> deelnemer(s) met e-mailadres
            </div>
        </div>
    </div>
</form>

@can('communicatie_view')
<form method="post" action="{{ route('intouch.registrations.communicatie.send') }}" id="send-form">
    @csrf
    <input type="hidden" name="distance_id" value="{{ $filters['distance_id'] ?? '' }}">
    <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">

    <div class="card mb-4">
        <div class="card-header">Bericht</div>
        <div class="card-body">
            <div class="mb-4">
                <label for="template_id" class="form-label">Template</label>
                <select id="template_id" name="template_id" class="form-select" required>
                    <option value="">– Kies een template –</option>
                    @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                    @endforeach
                    <option value="custom">Algemeen bericht (eigen tekst)</option>
                </select>
            </div>

            <div id="custom-fields" class="mb-4 d-none">
                <div class="mb-3">
                    <label for="custom_subject" class="form-label">Onderwerp *</label>
                    <input type="text" id="custom_subject" name="custom_subject" class="form-control" placeholder="Onderwerp van de e-mail">
                </div>
                <div class="mb-3">
                    <label for="custom_body" class="form-label">Bericht *</label>
                    <textarea id="custom_body" name="custom_body" class="form-control" rows="10" placeholder="Plaatshouders: @{{voornaam}}, @{{achternaam}}, @{{afstand}}, ..."></textarea>
                    <small class="text-muted">Gebruik @{{voornaam}}, @{{achternaam}}, @{{afstand}}, @{{edition_name}}, @{{start_datum}}, @{{eind_datum}}, @{{inschrijf_url}}, @{{routes_url}} voor persoonlijke inhoud.</small>
                </div>
            </div>

            <div class="mb-4">
                <label for="attachment" class="form-label">Bijlage (optioneel)</label>
                <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx">
                <small class="text-muted">Max 10 MB. PDF, afbeeldingen of Word-document.</small>
            </div>

            <div class="d-flex gap-2">
                <button type="button" id="btn-preview" class="btn btn-outline-primary" disabled>
                    Voorvertoning
                </button>
                @can('communicatie_send')
                <button type="submit" id="btn-send" class="btn btn-vierdaagse" disabled>
                    Verstuur naar {{ $recipientCount }} deelnemer(s)
                </button>
                @else
                <span class="text-muted small align-self-center">Je hebt geen rechten om e-mails te versturen.</span>
                @endcan
            </div>
        </div>
    </div>
</form>
@endcan

<div class="card">
    <div class="card-header">Verzendgeschiedenis</div>
    <div class="card-body">
        @forelse($logs as $log)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <strong>{{ $log->template_key }}</strong> – {{ $log->subject }}
                    <br>
                    <small class="text-muted">
                        {{ $log->sent_count }}/{{ $log->recipient_count }} verstuurd
                        @if(!$log->completed_at)
                            <span class="text-primary">(bezig)</span>
                        @elseif($log->failed_count > 0)
                            <span class="text-danger">({{ $log->failed_count }} mislukt)</span>
                        @endif
                        · {{ $log->created_at->format('d-m-Y H:i') }}
                        @if($log->sentByUser)
                            · {{ $log->sentByUser->name }}
                        @endif
                    </small>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Nog geen berichten verstuurd.</p>
        @endforelse
    </div>
</div>

<div class="modal fade" id="preview-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Voorvertoning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small" id="preview-registration"></p>
                <hr>
                <p><strong>Onderwerp:</strong></p>
                <p id="preview-subject" class="mb-3"></p>
                <p><strong>Bericht:</strong></p>
                <div id="preview-body" class="border p-3 bg-light rounded"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('template_id');
    const customFields = document.getElementById('custom-fields');
    if (!templateSelect) return;
    const btnPreview = document.getElementById('btn-preview');
    const btnSend = document.getElementById('btn-send');
    const recipientCount = {{ $recipientCount }};

    function updateButtons() {
        const hasTemplate = templateSelect.value && templateSelect.value !== '';
        const isCustom = templateSelect.value === 'custom';
        const customOk = !isCustom || (document.getElementById('custom_subject')?.value && document.getElementById('custom_body')?.value);
        btnPreview.disabled = !hasTemplate || recipientCount === 0 || (isCustom && !customOk);
        if (btnSend) btnSend.disabled = !hasTemplate || recipientCount === 0 || (isCustom && !customOk);
    }

    templateSelect.addEventListener('change', function() {
        customFields.classList.toggle('d-none', this.value !== 'custom');
        if (this.value !== 'custom') {
            document.getElementById('custom_subject').value = '';
            document.getElementById('custom_body').value = '';
        }
        updateButtons();
    });

    document.getElementById('custom_subject')?.addEventListener('input', updateButtons);
    document.getElementById('custom_body')?.addEventListener('input', updateButtons);
    updateButtons();

    btnPreview.addEventListener('click', function() {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value);
        formData.append('template_id', templateSelect.value);
        formData.append('distance_id', document.querySelector('input[name="distance_id"]').value);
        formData.append('status', document.querySelector('input[name="status"]').value);
        if (templateSelect.value === 'custom') {
            formData.append('custom_subject', document.getElementById('custom_subject').value);
            formData.append('custom_body', document.getElementById('custom_body').value);
        }

        fetch('{{ route('intouch.registrations.communicatie.preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            document.getElementById('preview-registration').textContent = 'Voorbeeld voor: ' + data.preview_registration;
            document.getElementById('preview-subject').textContent = data.subject;
            document.getElementById('preview-body').innerHTML = data.body;
            new bootstrap.Modal(document.getElementById('preview-modal')).show();
        })
        .catch(err => {
            alert('Voorvertoning laden mislukt. Controleer je filters (minimaal 1 deelnemer nodig).');
        });
    });

    const sendForm = document.getElementById('send-form');
    if (sendForm) sendForm.addEventListener('submit', function(e) {
        if (recipientCount === 0) {
            e.preventDefault();
            alert('Geen deelnemers geselecteerd.');
            return;
        }
        const btn = document.getElementById('btn-send');
        if (btn && !btn.disabled && !confirm('Weet je zeker dat je ' + recipientCount + ' e-mail(s) wilt versturen?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
