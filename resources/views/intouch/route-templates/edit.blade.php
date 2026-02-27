@extends('intouch.layout')

@section('title', 'Route bewerken in bibliotheek')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('intouch.route-templates.index') }}" class="text-muted text-decoration-none small">← Routebibliotheek</a>
        <h1 class="mb-1">Route bewerken</h1>
        <p class="text-muted small mb-0">{{ $template->distance->name ?? 'Onbekend' }} – {{ $template->title ?: 'Route' }}</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.route-templates.update', $template) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Afstand</label>
                <p class="form-control-plaintext mb-0">{{ $template->distance->name ?? '-' }}</p>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Titel (optioneel)</label>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $template->title) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Beschrijving (optioneel)</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $template->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <h6 class="mt-4 mb-2">Bestanden</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Word-document (bron)</label>
                    @if($template->word_path)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary">Aanwezig</span>
                        <a href="{{ $template->word_url }}" download class="btn btn-sm btn-outline-primary">Download</a>
                        <button type="submit" form="delete-word-form" class="btn btn-sm btn-outline-danger" onclick="return confirm('Word-document verwijderen?');">Verwijderen</button>
                    </div>
                    @else
                    <p class="text-muted small mb-1">Niet geüpload</p>
                    @endif
                    <input type="file" name="word" class="form-control" accept=".doc,.docx">
                    <small class="text-muted">Nieuwe upload vervangt bestaande. Max 20 MB.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">PDF (voorkant)</label>
                    @if($template->pdf_path)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success">Aanwezig</span>
                        <a href="{{ $template->pdf_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Bekijken</a>
                        <button type="submit" form="delete-pdf-form" class="btn btn-sm btn-outline-danger" onclick="return confirm('PDF verwijderen?');">Verwijderen</button>
                    </div>
                    @else
                    <p class="text-muted small mb-1">Niet geüpload</p>
                    @endif
                    <input type="file" name="pdf" class="form-control" accept=".pdf">
                    <small class="text-muted">Nieuwe upload vervangt bestaande. Max 10 MB.</small>
                </div>
            </div>

            <h6 class="mt-4 mb-2">Controlepunten</h6>
            <p class="text-muted small">Punten die meegekopieerd worden naar edities.</p>
            <div id="points-container">
                @php $pointsData = old('points', $template->points->pluck('name')->toArray()); @endphp
                @foreach($pointsData as $name)
                <div class="input-group mb-2 point-row">
                    <input type="text" name="points[]" class="form-control" value="{{ is_array($name) ? ($name['name'] ?? '') : $name }}" placeholder="Naam van het punt">
                    <button type="button" class="btn btn-outline-danger remove-point">×</button>
                </div>
                @endforeach
                @if($template->points->isEmpty() && empty(old('points')))
                <div class="input-group mb-2 point-row">
                    <input type="text" name="points[]" class="form-control" placeholder="Naam van het punt">
                    <button type="button" class="btn btn-outline-danger remove-point">×</button>
                </div>
                @endif
            </div>
            <button type="button" id="add-point" class="btn btn-outline-secondary btn-sm">+ Punt toevoegen</button>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
                <a href="{{ route('intouch.route-templates.index') }}" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
        @if($template->word_path)
        <form id="delete-word-form" method="post" action="{{ route('intouch.route-templates.delete-word', $template) }}" class="d-none">@csrf @method('DELETE')</form>
        @endif
        @if($template->pdf_path)
        <form id="delete-pdf-form" method="post" action="{{ route('intouch.route-templates.delete-pdf', $template) }}" class="d-none">@csrf @method('DELETE')</form>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let pointIndex = document.querySelectorAll('.point-row').length;
    document.getElementById('add-point').addEventListener('click', function() {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 point-row';
        div.innerHTML = '<input type="text" name="points[]" class="form-control" placeholder="Naam van het punt"><button type="button" class="btn btn-outline-danger remove-point">×</button>';
        document.getElementById('points-container').appendChild(div);
        pointIndex++;
    });
    document.getElementById('points-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-point')) e.target.closest('.point-row').remove();
    });
});
</script>
@endsection
