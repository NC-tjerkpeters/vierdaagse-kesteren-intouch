@extends('intouch.layout')

@section('title', 'Nieuwe editie')

@section('content')
<h1 class="mb-4">Start nieuwe editie</h1>

<div class="card" style="max-width: 540px">
    <div class="card-body">
        <p class="text-muted mb-4">
            Maak een nieuwe editie aan. Deze wordt automatisch de actieve editie. Er worden vier eventdagen (Dag 1 t/m 4) aangemaakt.
        </p>

        <form method="post" action="{{ route('intouch.editions.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Editienaam *</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', 'Editie ' . (date('Y') + 1)) }}" placeholder="bijv. Editie 2027" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label">Startdatum *</label>
                <input type="date" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                    value="{{ old('start_date', date('Y') . '-10-01') }}" required>
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Bijv. 1 oktober van het vorige jaar</small>
            </div>

            <div class="mb-4">
                <label for="end_date" class="form-label">Einddatum *</label>
                <input type="date" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                    value="{{ old('end_date', (date('Y') + 1) . '-09-30') }}" required>
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Bijv. 30 september van het editiejaar</small>
            </div>

            <button type="submit" class="btn btn-vierdaagse">Editie aanmaken</button>
            <a href="{{ route('intouch.editions.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
