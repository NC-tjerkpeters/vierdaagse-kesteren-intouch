@extends('intouch.layout')

@section('title', 'Financiën')

@section('content')
<h1 class="mb-4">Financiën</h1>

<div class="mb-4">
    <form method="get" action="{{ route('intouch.finance.index') }}" class="d-inline-flex align-items-center gap-2">
        <label for="edition_id" class="form-label mb-0">Editie:</label>
        <select name="edition_id" id="edition_id" class="form-select form-select-sm" style="width: auto" onchange="this.form.submit()">
            @foreach($editions as $e)
                <option value="{{ $e->id }}" @selected($edition->id === $e->id)>
                    {{ $e->name }} ({{ $e->start_date->format('Y') }}–{{ $e->end_date->format('Y') }})
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title text-muted small">Startsaldo</h5>
                <p class="mb-0 display-6">€ {{ number_format($openingBalance, 2, ',', '.') }}</p>
                <small class="text-muted d-block">Bank: € {{ number_format($openingBank, 2, ',', '.') }}</small>
                <small class="text-muted d-block">Kas: € {{ number_format($openingCash, 2, ',', '.') }}</small>
                @can('finances_edit')
                <button type="button" class="btn btn-link btn-sm p-0 mt-1" data-bs-toggle="collapse" data-bs-target="#edit-opening-balance">Wijzigen</button>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title text-muted small">Opbrengst deelnemers</h5>
                <p class="mb-0 display-6 text-success">€ {{ number_format($revenueDeelnemers, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title text-muted small">Opbrengst sponsors</h5>
                <p class="mb-0 display-6 text-success">€ {{ number_format($revenueSponsors, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body">
                <h5 class="card-title text-muted small">Totale kosten</h5>
                <p class="mb-0 display-6 text-danger">€ {{ number_format($totalCosts, 2, ',', '.') }}</p>
                <small class="text-muted d-block">Bank: € {{ number_format($costsBank, 2, ',', '.') }}</small>
                <small class="text-muted d-block">Kas: € {{ number_format($costsCash, 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
</div>

@can('finances_edit')
<div class="collapse mb-4" id="edit-opening-balance">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Startsaldo wijzigen</h6>
            <p class="text-muted small">Voer bank en kas apart in. Bij de eerste editie: je huidige saldo. Bij volgende edities: het eindsaldo van de vorige editie.</p>
            <form method="post" action="{{ route('intouch.finance.update-opening-balance') }}" class="d-flex gap-3 align-items-end flex-wrap">
                @csrf
                @method('PUT')
                <input type="hidden" name="edition_id" value="{{ $edition->id }}">
                <div class="mb-0">
                    <label for="opening_balance_bank" class="form-label small">Bank (€)</label>
                    <input type="number" name="opening_balance_bank" id="opening_balance_bank" class="form-control" step="0.01" value="{{ $openingBank }}" style="width: 120px">
                </div>
                <div class="mb-0">
                    <label for="opening_balance_cash" class="form-label small">Kas (€)</label>
                    <input type="number" name="opening_balance_cash" id="opening_balance_cash" class="form-control" step="0.01" value="{{ $openingCash }}" style="width: 120px">
                </div>
                <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
            </form>
        </div>
    </div>
</div>
@endcan

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card {{ $resultEdition >= 0 ? 'border-success' : 'border-danger' }}">
            <div class="card-body">
                <h5 class="card-title text-muted small">Resultaat deze editie</h5>
                <p class="mb-0 display-6 {{ $resultEdition >= 0 ? 'text-success' : 'text-danger' }}">
                    € {{ number_format($resultEdition, 2, ',', '.') }}
                </p>
                <small class="text-muted">Opbrengsten − kosten</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card {{ $closingBalance >= 0 ? 'border-success' : 'border-danger' }}">
            <div class="card-body">
                <h5 class="card-title text-muted small">Eindsaldo / Totaal</h5>
                <p class="mb-0 display-6 {{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                    € {{ number_format($closingBalance, 2, ',', '.') }}
                </p>
                <small class="text-muted d-block">Bank: € {{ number_format($closingBank, 2, ',', '.') }}</small>
                <small class="text-muted d-block">Kas: € {{ number_format($closingCash, 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Kosten</span>
        <div class="d-flex gap-2">
            @can('finances_edit')
            <form method="post" action="{{ route('intouch.finance.estimate-mollie', ['edition_id' => $edition->id]) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Mollie-kosten schatten</button>
            </form>
            <a href="{{ route('intouch.finance.cost.create', ['edition_id' => $edition->id]) }}" class="btn btn-light btn-sm">
                Kost toevoegen
            </a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Omschrijving</th>
                    <th>Categorie</th>
                    <th class="text-end">Bedrag</th>
                    @can('finances_edit')
                    <th></th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @php $categories = \App\Models\CostEntry::categories(); @endphp
                @forelse($costsByCategory->flatten()->sortByDesc('cost_date') as $c)
                    <tr>
                        <td>{{ $c->cost_date->format('d-m-Y') }}</td>
                        <td>{{ $c->description }}</td>
                        <td>{{ $categories[$c->category] ?? $c->category }}</td>
                        <td>{{ (\App\Models\CostEntry::paymentMethods())[$c->payment_method ?? 'bank'] ?? $c->payment_method }}</td>
                        <td class="text-end">€ {{ number_format($c->amount, 2, ',', '.') }}</td>
                        @can('finances_edit')
                        <td class="text-end">
                            <a href="{{ route('intouch.finance.cost.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Bewerken</a>
                            <form method="post" action="{{ route('intouch.finance.cost.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Kost verwijderen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('finances_edit') ? 6 : 5 }}" class="text-muted">Geen kosten opgevoerd.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted">
    <small>Startsaldo = bank + kas apart. Opbrengsten (deelnemers, sponsors) gaan altijd via de bank. Bij kosten: geef aan of het via bank of kas is betaald. Mollie-kosten altijd bank.</small>
</p>
@endsection
