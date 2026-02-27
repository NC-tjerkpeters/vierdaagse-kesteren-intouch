@extends('intouch.layout')

@section('title', 'Route aanmaken')

@section('content')
<h1 class="mb-4">Route aanmaken</h1>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.walk-routes.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="distance_id" class="form-label">Afstand</label>
                <select id="distance_id" name="distance_id" class="form-select @error('distance_id') is-invalid @enderror" required>
                    <option value="">– Kies een afstand –</option>
                    @foreach($distances as $d)
                        <option value="{{ $d->id }}" @selected(old('distance_id') == $d->id)>{{ $d->name }} ({{ $d->kilometers }} km)</option>
                    @endforeach
                </select>
                @error('distance_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Titel (optioneel)</label>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Bijv. Route 5 km – Via Kesteren">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Beschrijving (optioneel)</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Actief op dagen</label>
                <p class="text-muted small mb-2">Geen vinkjes = actief op alle dagen. Alleen vinkjes zetten bij de dagen waarop deze route loopt.</p>
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
                <label for="pdf" class="form-label">PDF routekaart</label>
                <input type="file" id="pdf" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept=".pdf">
                <small class="text-muted">Max 10 MB. Je kunt later ook een PDF toevoegen.</small>
                @error('pdf')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vierdaagse">Aanmaken</button>
                <a href="{{ route('intouch.walk-routes.index') }}" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
    </div>
</div>
@endsection
