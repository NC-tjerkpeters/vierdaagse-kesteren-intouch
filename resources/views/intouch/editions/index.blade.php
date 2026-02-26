@extends('intouch.layout')

@section('title', 'Edities')

@section('content')
<h1 class="mb-4">Edities</h1>

@if($activeEdition)
<div class="alert alert-success mb-4">
    <strong>Actieve editie:</strong> {{ $activeEdition->name }}
    ({{ $activeEdition->start_date->format('d-m-Y') }} – {{ $activeEdition->end_date->format('d-m-Y') }})
</div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Alle edities</span>
        @can('editions_manage')
        <a href="{{ route('intouch.beheer.editions.create') }}" class="btn btn-light btn-sm">Start nieuwe editie</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Periode</th>
                    <th>Inschrijvingen</th>
                    <th>Sponsors</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($editions as $e)
                    <tr>
                        <td>{{ $e->name }}</td>
                        <td>{{ $e->start_date->format('d-m-Y') }} – {{ $e->end_date->format('d-m-Y') }}</td>
                        <td>{{ $e->registrations_count }}</td>
                        <td>{{ $e->sponsors_count }}</td>
                        <td>
                            @if($e->is_active)
                                <span class="badge bg-success">Actief</span>
                            @else
                                <span class="badge bg-secondary">Archief</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">Nog geen edities.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted">
    <small>Edities bepalen welke inschrijvingen, sponsors en scan-data bij welke editie horen. De actieve editie wordt gebruikt voor nieuwe inschrijvingen en bij het starten van een nieuwe editie.</small>
</p>
@endsection
