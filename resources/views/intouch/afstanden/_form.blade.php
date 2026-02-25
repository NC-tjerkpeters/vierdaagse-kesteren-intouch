@php
    $distance = $distance ?? null;
@endphp
<div class="mb-3">
    <label for="name" class="form-label">Naam</label>
    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $distance?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="kilometers" class="form-label">Kilometers</label>
        <input type="number" id="kilometers" name="kilometers" class="form-control @error('kilometers') is-invalid @enderror" value="{{ old('kilometers', $distance?->kilometers) }}" step="0.1" min="0" required>
        @error('kilometers')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="price" class="form-label">Prijs (€)</label>
        <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $distance?->price) }}" step="0.01" min="0" required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="sort_order" class="form-label">Volgorde</label>
        <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $distance?->sort_order ?? 0) }}" min="0">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Loopt op avonden</label>
    <p class="text-muted small mb-2">Geen vinkjes = loopt op alle avonden. Alleen vinkjes zetten bij de avonden waarop deze afstand loopt (bijv. 2,5 km alleen Dag 2 en 4).</p>
    @php
        $eventDays = $eventDays ?? \App\Models\EventDay::query()->orderBy('sort_order')->get();
        $selected = old('event_day_sort_orders', $distance?->event_day_sort_orders ?? null);
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
<div class="mb-3 form-check">
    <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" @checked(old('is_active', $distance?->is_active ?? true))>
    <label class="form-check-label" for="is_active">Actief (zichtbaar bij inschrijven)</label>
</div>
