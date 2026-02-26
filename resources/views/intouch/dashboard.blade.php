@extends('intouch.layout')

@section('title', 'Dashboard')

@section('content')
<h1 class="mb-4">Dashboard</h1>

@if($edition)
<div class="alert alert-light border mb-4">
    <strong>{{ $edition->name }}</strong>
    ({{ $edition->start_date->format('d-m-Y') }} – {{ $edition->end_date->format('d-m-Y') }})
    @if($edition->is_active)
        <span class="badge bg-success ms-2">Actief</span>
    @endif
</div>
@else
<div class="alert alert-warning mb-4">
    Geen editie actief. <a href="{{ route('intouch.beheer.editions.index') }}">Start een nieuwe editie</a> of selecteer een editie in de navigatie.
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted small">Inschrijvingen</h5>
                <p class="mb-0 display-6">{{ $totalRegistrations }}</p>
                <small class="text-muted">{{ $paidCount }} betaald</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted small">Met medaille</h5>
                <p class="mb-0 display-6">{{ $withMedal }}</p>
            </div>
        </div>
    </div>
    @can('finances_view')
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title text-muted small">Resultaat</h5>
                <p class="mb-0 display-6 {{ $result >= 0 ? 'text-success' : 'text-danger' }}">
                    € {{ number_format($result, 0, ',', '.') }}
                </p>
                <a href="{{ route('intouch.finance.index') }}" class="small">Financiën →</a>
            </div>
        </div>
    </div>
    @endcan
    @can('sponsors_view')
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted small">Sponsors</h5>
                <p class="mb-0 display-6">€ {{ number_format($sponsorTotaal, 0, ',', '.') }}</p>
                <div class="progress mt-1" style="height:6px">
                    <div class="progress-bar bg-success" style="width:{{ min($sponsorProgress, 100) }}%"></div>
                </div>
                <a href="{{ route('intouch.sponsors.index') }}" class="small">Details →</a>
            </div>
        </div>
    </div>
    @endcan
</div>

<h2 class="h5 mb-3">Betaald per afstand</h2>
<div class="card mb-4">
    <div class="card-body">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Afstand</th>
                    <th class="text-end">Aantal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distances as $d)
                    <tr>
                        <td>{{ $d->name }}</td>
                        <td class="text-end">{{ $byDistance->get($d->id)?->total ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($edition)
<div class="row g-3">
    @can('inschrijvingen_view')
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Inschrijvingen</h5>
                <p class="card-text text-muted">Overzicht, medaille-bestelling en export.</p>
                <a href="{{ route('intouch.registrations.index') }}" class="btn btn-vierdaagse btn-sm">Open overzicht</a>
            </div>
        </div>
    </div>
    @endcan
    @can('loopoverzicht_view')
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Loopoverzicht</h5>
                <p class="card-text text-muted">Scans en voortgang per avond.</p>
                <a href="{{ route('intouch.scan-overview.index') }}" class="btn btn-vierdaagse btn-sm">Open overzicht</a>
            </div>
        </div>
    </div>
    @endcan
    @can('finances_view')
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Financiën</h5>
                <p class="card-text text-muted">Opbrengsten, kosten en resultaat.</p>
                <a href="{{ route('intouch.finance.index') }}" class="btn btn-vierdaagse btn-sm">Open financiën</a>
            </div>
        </div>
    </div>
    @endcan
</div>
@endif
@endsection
