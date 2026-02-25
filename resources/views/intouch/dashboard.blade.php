@extends('intouch.layout')

@section('title', 'Dashboard')

@section('content')
<h1 class="mb-4">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Totaal inschrijvingen</h5>
                <p class="mb-0 display-6">{{ $totalRegistrations }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Betaald</h5>
                <p class="mb-0 display-6">{{ $paidCount }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-muted">Met medaille</h5>
                <p class="mb-0 display-6">{{ $withMedal }}</p>
            </div>
        </div>
    </div>
</div>

<h2 class="h5 mb-3">Betaald per afstand</h2>
<div class="card">
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
@endsection
