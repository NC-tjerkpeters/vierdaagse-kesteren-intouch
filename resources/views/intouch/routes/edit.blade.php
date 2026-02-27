@extends('intouch.layout')

@section('title', 'Route bewerken')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('intouch.walk-routes.index') }}" class="text-muted text-decoration-none small">← Routes</a>
        <h1 class="mb-1">Route bewerken</h1>
        <p class="text-muted small mb-0">{{ $walkRoute->distance->name ?? 'Onbekend' }} – {{ $walkRoute->title ?: 'Route' }}</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.walk-routes.update', $walkRoute) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Afstand</label>
                <p class="form-control-plaintext mb-0">{{ $walkRoute->distance->name ?? '-' }}</p>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Titel (optioneel)</label>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $walkRoute->title) }}">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Beschrijving (optioneel)</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $walkRoute->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Actief op dagen</label>
                <p class="text-muted small mb-2">Geen vinkjes = actief op alle dagen. Alleen vinkjes zetten bij de dagen waarop deze route loopt.</p>
                @php
                    $selected = old('event_day_sort_orders', $walkRoute->event_day_sort_orders ?? []);
                    $selected = is_array($selected) ? array_map('intval', $selected) : [];
                    $showAll = $selected === [] || $selected === null;
                @endphp
                <div class="d-flex flex-wrap gap-3">
                    @foreach($eventDays as $day)
                        <div class="form-check">
                            <input type="checkbox" id="event_day_{{ $day->sort_order }}" name="event_day_sort_orders[]" class="form-check-input" value="{{ $day->sort_order }}"
                                @checked($showAll || in_array((int) $day->sort_order, $selected, true))>
                            <label class="form-check-label" for="event_day_{{ $day->sort_order }}">{{ $day->name }}</label>
                        </div>
                    @endforeach
                </div>
                @error('event_day_sort_orders')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="pdf" class="form-label">PDF routekaart</label>
                @if($walkRoute->pdf_path)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success">PDF aanwezig</span>
                        <a href="{{ route('intouch.walk-routes.pdf', $walkRoute) }}" target="_blank" class="btn btn-sm btn-outline-primary">Bekijken</a>
                        <button type="submit" form="delete-pdf-form" class="btn btn-sm btn-outline-danger" onclick="return confirm('PDF verwijderen?');">Verwijderen</button>
                    </div>
                @endif
                <input type="file" id="pdf" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept=".pdf">
                <small class="text-muted">Max 10 MB. Nieuwe upload vervangt bestaande PDF.</small>
                @error('pdf')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h6 class="mt-4 mb-2">Controlepunten</h6>
            <p class="text-muted small">Punten die wandelaars kunnen afstrepen op hun route. Volgorde kan je aanpassen door rijen te herschikken.</p>
            <div id="points-container">
                @foreach(old('points', $walkRoute->points->pluck('name')->toArray()) as $name)
                    <div class="input-group mb-2 point-row">
                        <input type="text" name="points[]" class="form-control" value="{{ is_array($name) ? ($name['name'] ?? '') : $name }}" placeholder="Naam van het punt">
                        <button type="button" class="btn btn-outline-danger remove-point" title="Verwijderen">×</button>
                    </div>
                @endforeach
                @if($walkRoute->points->isEmpty() && empty(old('points')))
                    <div class="input-group mb-2 point-row">
                        <input type="text" name="points[]" class="form-control" placeholder="Naam van het punt">
                        <button type="button" class="btn btn-outline-danger remove-point" title="Verwijderen">×</button>
                    </div>
                @endif
            </div>
            <button type="button" id="add-point" class="btn btn-outline-secondary btn-sm">+ Punt toevoegen</button>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
                <a href="{{ route('intouch.walk-routes.index') }}" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
        @if($walkRoute->pdf_path)
        <form id="delete-pdf-form" method="post" action="{{ route('intouch.walk-routes.delete-pdf', $walkRoute) }}" class="d-none">@csrf @method('DELETE')</form>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let pointIndex = document.querySelectorAll('.point-row').length;
    document.getElementById('add-point').addEventListener('click', function() {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 point-row';
        div.innerHTML = '<input type="text" name="points[]" class="form-control" placeholder="Naam van het punt">' +
            '<button type="button" class="btn btn-outline-danger remove-point" title="Verwijderen">×</button>';
        document.getElementById('points-container').appendChild(div);
        pointIndex++;
    });
    document.getElementById('points-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-point')) {
            e.target.closest('.point-row').remove();
        }
    });
});
</script>
@endsection
