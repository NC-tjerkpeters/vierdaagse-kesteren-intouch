@extends('intouch.layout')

@section('title', 'Inschrijvingen')

@section('content')
<h1 class="mb-4">Inschrijvingen</h1>

<form method="get" action="{{ route('intouch.registrations.index') }}" class="card mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label for="q" class="form-label">Zoeken</label>
                <input type="text" id="q" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Naam, e-mail">
            </div>
            <div class="col-md-2">
                <label for="distance_id" class="form-label">Afstand</label>
                <select id="distance_id" name="distance_id" class="form-select form-select-sm">
                    <option value="">Alle</option>
                    @foreach($distances as $d)
                        <option value="{{ $d->id }}" @selected(request('distance_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Betaling</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="">Alle</option>
                    <option value="paid" @selected(request('status') === 'paid')>Betaald</option>
                    <option value="open" @selected(request('status') === 'open')>Niet betaald</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="medal" class="form-label">Medaille</label>
                <select id="medal" name="medal" class="form-select form-select-sm">
                    <option value="">Alle</option>
                    <option value="yes" @selected(request('medal') === 'yes')>Ja</option>
                    <option value="no" @selected(request('medal') === 'no')>Nee</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('intouch.registrations.index') }}" class="btn btn-outline-secondary btn-sm">Wissen</a>
            </div>
            <div class="col-md-2 text-end">
                @can('inschrijvingen_export')
                <a href="{{ route('intouch.registrations.export', request()->query()) }}" class="btn btn-success btn-sm">Export CSV</a>
                @endcan
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>E-mail</th>
                    <th>Afstand</th>
                    <th>Medaille</th>
                    <th>Betaling</th>
                    <th>Datum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->first_name }} {{ $r->last_name }}</td>
                        <td>{{ $r->email }}</td>
                        <td>{{ $r->distance->name ?? '-' }}</td>
                        <td>{{ $r->wants_medal ? 'Ja' . ($r->medal_number ? " (#{$r->medal_number})" : '') : 'Nee' }}</td>
                        <td>
                            @if($r->mollie_payment_status === 'paid')
                                <span class="badge bg-success">Betaald</span>
                            @else
                                <span class="badge bg-secondary">{{ $r->mollie_payment_status }}</span>
                            @endif
                        </td>
                        <td>{{ $r->created_at->format('d-m-Y H:i') }}</td>
                        <td><a href="{{ route('intouch.registrations.show', $r) }}" class="btn btn-sm btn-outline-primary">Bekijken</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-muted">Geen inschrijvingen gevonden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($registrations->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $registrations->withQueryString()->links() }}
    </div>
@endif
@endsection
