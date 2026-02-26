@extends('intouch.layout')

@section('title', 'Nieuwe editie')

@section('content')
<h1 class="mb-4">Start nieuwe editie</h1>

<div class="card" style="max-width: 540px">
    <div class="card-body">
        <p class="text-muted mb-4">
            Maak een nieuwe editie aan. Deze wordt automatisch de actieve editie. Er worden vier eventdagen (Dag 1 t/m 4) aangemaakt.
        </p>

        <form method="post" action="{{ route('intouch.beheer.editions.store') }}">
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

            <div class="mb-3">
                <label for="end_date" class="form-label">Einddatum *</label>
                <input type="date" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                    value="{{ old('end_date', (date('Y') + 1) . '-09-30') }}" required>
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Bijv. 30 september van het editiejaar</small>
            </div>

            <div class="mb-4">
                <label class="form-label">Startsaldo (€)</label>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div>
                        <label for="opening_balance_bank" class="form-label small mb-0">Bank</label>
                        <input type="number" id="opening_balance_bank" name="opening_balance_bank" class="form-control @error('opening_balance_bank') is-invalid @enderror"
                            value="{{ old('opening_balance_bank', $suggestedBank ?? 0) }}" step="0.01" min="0" placeholder="0,00" style="max-width: 120px">
                    </div>
                    <div>
                        <label for="opening_balance_cash" class="form-label small mb-0">Kas</label>
                        <input type="number" id="opening_balance_cash" name="opening_balance_cash" class="form-control @error('opening_balance_cash') is-invalid @enderror"
                            value="{{ old('opening_balance_cash', $suggestedCash ?? 0) }}" step="0.01" min="0" placeholder="0,00" style="max-width: 120px">
                    </div>
                    @if($previousEdition ?? null)
                    <button type="button" class="btn btn-outline-secondary btn-sm align-self-end" onclick="document.getElementById('opening_balance_bank').value = {{ number_format($suggestedBank, 2, '.', '') }}; document.getElementById('opening_balance_cash').value = {{ number_format($suggestedCash, 2, '.', '') }}">Neem over van {{ $previousEdition->name }}</button>
                    @endif
                </div>
                @error('opening_balance_bank')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('opening_balance_cash')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <small class="text-muted d-block mt-1">
                    @if($previousEdition ?? null)
                        Eindsaldo {{ $previousEdition->name }}: bank € {{ number_format($suggestedBank, 2, ',', '.') }}, kas € {{ number_format($suggestedCash, 2, ',', '.') }}. Klik om over te nemen.
                    @else
                        Bij de eerste editie: voer bank + kas in. Daarna ook wijzigbaar via Financiën.
                    @endif
                </small>
            </div>

            <button type="submit" class="btn btn-vierdaagse">Editie aanmaken</button>
            <a href="{{ route('intouch.beheer.editions.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
