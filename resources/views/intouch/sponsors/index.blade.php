@extends('intouch.layout')

@section('title', 'Sponsors')

@section('content')
<h1 class="mb-4">Sponsors – Vrienden</h1>

{{-- Progress + Doel --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <div class="fw-bold">Opgehaald</div>
                <div style="font-size:1.8rem" class="fw-bold">
                    € {{ number_format($totaalOpgehaald, 2, ',', '.') }}
                </div>
                <div class="text-muted">{{ $aantalBetaald }} betalingen</div>
            </div>
            <div class="text-md-end">
                <div class="fw-bold">Doel</div>
                <div style="font-size:1.2rem" class="fw-bold">
                    € {{ number_format($doelbedrag, 2, ',', '.') }}
                </div>
                <div class="text-muted">
                    Nog nodig: € {{ number_format($nogNodig, 2, ',', '.') }}
                </div>
            </div>
        </div>

        @php
            $barClass = $progress >= 75 ? 'bg-success' : ($progress >= 40 ? 'bg-warning' : 'bg-danger');
        @endphp
        <div class="progress mt-3" style="height:16px">
            <div class="progress-bar {{ $barClass }}" style="width:{{ $progress }}%" role="progressbar"></div>
        </div>
    </div>
</div>

{{-- Filter + Zoek --}}
<form method="get" action="{{ route('intouch.sponsors.index') }}" class="card mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">Zoeken</label>
                <input type="text" id="q" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Bedrijf, naam, e-mail, betaling ID">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="all" @selected($filter === 'all')>Alle</option>
                    <option value="paid" @selected($filter === 'paid')>Betaald</option>
                    <option value="open" @selected($filter === 'open')>Open</option>
                    <option value="pending" @selected($filter === 'pending')>Pending</option>
                    <option value="authorized" @selected($filter === 'authorized')>Authorized</option>
                    <option value="failed" @selected($filter === 'failed')>Failed</option>
                    <option value="canceled" @selected($filter === 'canceled')>Canceled</option>
                    <option value="expired" @selected($filter === 'expired')>Expired</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('intouch.sponsors.index') }}" class="btn btn-outline-secondary btn-sm">Wissen</a>
            </div>
            <div class="col-md-2 text-end">
                @can('sponsors_create')
                <a href="{{ route('intouch.sponsors.create') }}" class="btn btn-success btn-sm">Sponsor toevoegen</a>
                @endcan
            </div>
        </div>
    </div>
</form>

{{-- Mobile cards --}}
<div class="d-md-none">
    @foreach($sponsors as $s)
        <div class="card mb-2 shadow-sm">
            <div class="card-body">
                <div class="fw-bold">{{ $s->display_name }}</div>
                <div>@include('intouch.sponsors._status_badge', ['status' => $s->betaalstatus])</div>
                <div>€ {{ number_format($s->bedrag, 2, ',', '.') }}</div>
                <div class="mt-2">
                    @can('sponsors_edit')
                    <a href="{{ route('intouch.sponsors.edit', $s) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                    @if($s->betaalstatus === 'paid' && $s->invoice_id)
                    <form method="post" action="{{ route('intouch.sponsors.resend-receipt', $s) }}" class="d-inline" onsubmit="return confirm('Kwitantie opnieuw versturen?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Kwitantie versturen</button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Desktop table --}}
<div class="card shadow-sm d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Bedrijf</th>
                    <th>Naam</th>
                    <th>E-mail</th>
                    <th>Bedrag</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sponsors as $s)
                    <tr>
                        <td>{{ $s->bedrijfsnaam ?: '–' }}</td>
                        <td>{{ $s->voornaam }} {{ $s->achternaam }}</td>
                        <td>{{ $s->email }}</td>
                        <td>€ {{ number_format($s->bedrag, 2, ',', '.') }}</td>
                        <td>@include('intouch.sponsors._status_badge', ['status' => $s->betaalstatus])</td>
                        <td>
                            @can('sponsors_edit')
                            <a href="{{ route('intouch.sponsors.edit', $s) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                            @if($s->betaalstatus === 'paid' && $s->invoice_id)
                            <form method="post" action="{{ route('intouch.sponsors.resend-receipt', $s) }}" class="d-inline" onsubmit="return confirm('Kwitantie opnieuw versturen naar {{ $s->email }}?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Kwitantie opnieuw versturen">Kwitantie versturen</button>
                            </form>
                            @endif
                            @endcan
                            @can('sponsors_delete')
                            <form method="post" action="{{ route('intouch.sponsors.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Sponsor verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted">Geen sponsors gevonden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($sponsors->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $sponsors->withQueryString()->links() }}
    </div>
@endif
@endsection
