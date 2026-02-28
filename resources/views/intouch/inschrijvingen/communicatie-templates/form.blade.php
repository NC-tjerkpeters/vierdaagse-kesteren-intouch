@extends('intouch.layout')

@section('title', $template ? 'Template bewerken' : 'Nieuwe template')

@section('content')
<div class="mb-4">
    <a href="{{ route('intouch.registrations.communicatie.templates') }}" class="text-muted text-decoration-none small">← E-mailtemplates</a>
    <h1 class="mb-1">{{ $template ? 'Template bewerken' : 'Nieuwe template' }}</h1>
    <p class="text-muted small mb-0">HTML is toegestaan. Gebruik @{{voornaam}}, @{{achternaam}}, @{{afstand}}, @{{edition_name}}, @{{start_datum}}, @{{eind_datum}}, @{{inschrijf_url}}, @{{routes_url}} voor persoonlijke inhoud.</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ $template ? route('intouch.registrations.communicatie.templates.update', $template) : route('intouch.registrations.communicatie.templates.store') }}">
            @csrf
            @if($template) @method('PUT') @endif

            <div class="mb-3">
                <label for="name" class="form-label">Naam *</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $template?->name) }}" placeholder="bijv. Voorbereiding – week voor start" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Onderwerp *</label>
                <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror"
                    value="{{ old('subject', $template?->subject) }}" placeholder="bijv. Over een week begint de Vierdaagse @{{edition_name}}!" required>
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="body" class="form-label">Bericht (HTML) *</label>
                <div id="body-editor" class="border rounded bg-white" style="min-height: 200px;"></div>
                <textarea id="body" name="body" class="d-none @error('body') is-invalid @enderror" required>{{ old('body', $template?->body) }}</textarea>
                @error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <small class="text-muted">Plaatshouders: @{{voornaam}}, @{{achternaam}}, @{{afstand}}, @{{edition_name}}, @{{start_datum}}, @{{eind_datum}}, @{{inschrijf_url}}, @{{routes_url}}</small>
            </div>

            <button type="submit" class="btn btn-vierdaagse">{{ $template ? 'Bijwerken' : 'Aanmaken' }}</button>
            <a href="{{ route('intouch.registrations.communicatie.templates') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorEl = document.getElementById('body-editor');
    const textarea = document.getElementById('body');
    if (!editorEl || !textarea) return;

    const quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Schrijf je bericht...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    quill.root.innerHTML = textarea.value || '';

    quill.on('text-change', function() {
        textarea.value = quill.root.innerHTML;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    });

    document.querySelector('form').addEventListener('submit', function() {
        textarea.value = quill.root.innerHTML;
    });
});
</script>
@endpush
