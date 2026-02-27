@extends('intouch.layout')

@section('title', 'Route toevoegen uit bibliotheek')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('intouch.walk-routes.index') }}" class="text-muted text-decoration-none small">← Routes</a>
        <h1 class="mb-1">Route toevoegen uit bibliotheek</h1>
        <p class="text-muted small mb-0">Kies een route uit de bibliotheek voor {{ $edition->name }}. De PDF wordt gekopieerd; je kunt ook een andere PDF uploaden.</p>
    </div>
    <a href="{{ route('intouch.route-templates.index') }}" class="btn btn-outline-secondary">Bibliotheek beheren</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.walk-routes.add-from-library') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="route_template_id" class="form-label">Route uit bibliotheek</label>
                <select id="route_template_id" name="route_template_id" class="form-select @error('route_template_id') is-invalid @enderror" required>
                    <option value="">– Kies een route –</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}" @selected(old('route_template_id') == $t->id)
                            data-has-pdf="{{ $t->pdf_path ? '1' : '0' }}"
                            data-distance="{{ $t->distance->name ?? '' }}"
                            data-title="{{ e($t->title ?? '') }}">
                            {{ $t->distance->name ?? '-' }} – {{ $t->title ?: 'Route' }}
                            @if($t->pdf_path)
                                (heeft PDF)
                            @else
                                (geen PDF)
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('route_template_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Actief op dagen</label>
                <p class="text-muted small mb-2">Geen vinkjes = actief op alle dagen. Alleen vinkjes bij de dagen waarop deze route loopt.</p>
                @php
                    $selected = old('event_day_sort_orders', []);
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
                <label for="pdf" class="form-label">PDF routekaart (optioneel)</label>
                <p class="text-muted small mb-2">Laat leeg om de PDF uit de bibliotheek te gebruiken. Upload een bestand om een andere PDF te gebruiken voor deze editie.</p>
                <input type="file" id="pdf" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept=".pdf">
                <small class="text-muted">Max 10 MB.</small>
                @error('pdf')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vierdaagse">Toevoegen aan editie</button>
                <a href="{{ route('intouch.walk-routes.index') }}" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
    </div>
</div>

@if($templates->isEmpty())
<div class="alert alert-info">
    Er staan nog geen routes in de bibliotheek. <a href="{{ route('intouch.route-templates.create') }}">Voeg eerst routes toe aan de bibliotheek</a>.
</div>
@endif
@endsection
