@extends('intouch.layout')

@section('title', $cost ? 'Kost bewerken' : 'Kost toevoegen')

@section('content')
<h1 class="mb-4">{{ $cost ? 'Kost bewerken' : 'Kost toevoegen' }}</h1>

<div class="card" style="max-width: 540px">
    <div class="card-body">
        <form method="post" action="{{ $cost ? route('intouch.finance.cost.update', $cost) : route('intouch.finance.cost.store') }}">
            @csrf
            @if($cost) @method('PUT') @endif

            @if(!$cost)
            <div class="mb-3">
                <label for="edition_id" class="form-label">Editie *</label>
                <select name="edition_id" id="edition_id" class="form-select @error('edition_id') is-invalid @enderror" required>
                    @foreach($editions as $e)
                        <option value="{{ $e->id }}" @selected(old('edition_id', $edition->id) == $e->id)>
                            {{ $e->name }}
                        </option>
                    @endforeach
                </select>
                @error('edition_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endif

            <div class="mb-3">
                <label for="description" class="form-label">Omschrijving *</label>
                <input type="text" id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                    value="{{ old('description', $cost?->description) }}" required>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Bedrag (€) *</label>
                <input type="number" id="amount" name="amount" class="form-control @error('amount') is-invalid @enderror"
                    value="{{ old('amount', $cost?->amount) }}" step="0.01" min="0" required>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="payment_method" class="form-label">Betaald via *</label>
                <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                    @foreach(\App\Models\CostEntry::paymentMethods() as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_method', $cost?->payment_method ?? 'bank') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Is deze kost via de bank of contant (kas) betaald?</small>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Categorie *</label>
                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                    @foreach(\App\Models\CostEntry::categories() as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $cost?->category ?? 'overig') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="cost_date" class="form-label">Datum *</label>
                <input type="date" id="cost_date" name="cost_date" class="form-control @error('cost_date') is-invalid @enderror"
                    value="{{ old('cost_date', $cost?->cost_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                @error('cost_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-vierdaagse">{{ $cost ? 'Bijwerken' : 'Toevoegen' }}</button>
            <a href="{{ route('intouch.finance.index', ['edition_id' => $edition->id]) }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
