@extends('intouch.layout')

@section('title', $volunteer ? 'Vrijwilliger bewerken' : 'Vrijwilliger toevoegen')

@section('content')
<div class="mb-4">
    <a href="{{ route('intouch.volunteers.index') }}" class="text-muted text-decoration-none small">← Vrijwilligersrooster</a>
    <h1 class="mb-1">{{ $volunteer ? 'Vrijwilliger bewerken' : 'Vrijwilliger toevoegen' }}</h1>
    <p class="text-muted small mb-0">{{ $edition->name }}</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ $volunteer ? route('intouch.volunteers.update', $volunteer) : route('intouch.volunteers.store') }}">
            @csrf
            @if($volunteer) @method('PUT') @endif

            <div class="mb-3">
                <label for="name" class="form-label">Naam *</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $volunteer?->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail *</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $volunteer?->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Telefoon</label>
                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $volunteer?->phone) }}" placeholder="06 12 34 56 78">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Beschikbaar op</label>
                <p class="text-muted small mb-2">Op welke avonden is deze vrijwilliger beschikbaar?</p>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($eventDays as $day)
                    <div class="form-check">
                        <input type="checkbox" id="day_{{ $day->id }}" name="available_days[]" class="form-check-input" value="{{ $day->id }}"
                            @checked(in_array($day->id, old('available_days', $volunteer?->availabilities->pluck('event_day_id')->toArray() ?? [])))>
                        <label class="form-check-label" for="day_{{ $day->id }}">{{ $day->name }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label">Opmerkingen</label>
                <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $volunteer?->notes) }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-vierdaagse">{{ $volunteer ? 'Bijwerken' : 'Toevoegen' }}</button>
            <a href="{{ route('intouch.volunteers.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
