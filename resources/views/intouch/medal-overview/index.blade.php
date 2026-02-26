@extends('intouch.layout')

@section('title', 'Medaille-bestelling')

@section('content')
<h1 class="mb-4">Medaille-bestelling</h1>

<p class="text-muted mb-4">
    Overzicht van het aantal medailles dat besteld moet worden, op basis van betaalde inschrijvingen waarbij een medaille is aangevraagd.
</p>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="fw-bold text-muted small text-uppercase">Totaal medailles</div>
                <div class="fs-2 fw-bold">{{ $totaalMedailles }}</div>
                <div class="text-muted">te bestellen</div>
            </div>
            <div class="col-md-6">
                <div class="fw-bold text-muted small text-uppercase">Deelnemers met medaille</div>
                <div class="fs-2 fw-bold">{{ $totaalMetMedaille }}</div>
                <div class="text-muted">betaald</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Per medaillenummer</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Medaillenummer</th>
                    <th class="text-end">Aantal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medals as $m)
                    <tr>
                        <td>{{ $m['label'] }}</td>
                        <td class="text-end fw-bold">{{ $m['aantal'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-muted">Nog geen medailles aangevraagd door betaalde deelnemers.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($medals->isNotEmpty())
            <tfoot class="table-secondary">
                <tr>
                    <th>Totaal</th>
                    <th class="text-end">{{ $totaalMedailles }}</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<p class="mt-3">
    <a href="{{ route('intouch.registrations.index') }}" class="text-decoration-none">← Terug naar inschrijvingen</a>
</p>
@endsection
