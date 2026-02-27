@extends('intouch.layout')

@section('title', 'Routes – ' . $edition->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Routes – {{ $edition->name }}</h1>
        <p class="text-muted small mb-0">Beheer wandelroutes per afstand voor de editie</p>
    </div>
    @can('routes_manage')
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <a href="{{ route('intouch.walk-routes.create') }}" class="btn btn-vierdaagse">Nieuwe route</a>
        <a href="{{ route('intouch.walk-routes.add-from-library-form') }}" class="btn btn-outline-primary">Toevoegen uit bibliotheek</a>
        <a href="{{ route('intouch.route-templates.index') }}" class="btn btn-outline-secondary">Routebibliotheek</a>
    </div>
    @endcan
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Volgorde</th>
                    <th>Afstand</th>
                    <th>Titel</th>
                    <th>Dagen</th>
                    <th>Punten</th>
                    <th>PDF</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($walkRoutes as $route)
                <tr>
                    <td>{{ $route->sort_order }}</td>
                    <td>{{ $route->distance->name ?? '-' }}</td>
                    <td>{{ $route->title ?: '-' }}</td>
                    <td>
                        @if($route->event_day_sort_orders === null || $route->event_day_sort_orders === [])
                            Alle dagen
                        @else
                            {{ collect($route->event_day_sort_orders)->map(fn($n) => $eventDays->get($n)?->name ?? 'Dag '.$n)->join(', ') }}
                        @endif
                    </td>
                    <td>{{ $route->points->count() }}</td>
                    <td>
                        @if($route->pdf_path)
                        <span class="badge bg-success">Ja</span>
                        @else
                        <span class="badge bg-secondary">Nee</span>
                        @endif
                    </td>
<td class="text-end">
                            @can('routes_manage')
                            <a href="{{ route('intouch.walk-routes.edit', $route) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                            <form method="post" action="{{ route('intouch.walk-routes.destroy', $route) }}" class="d-inline" onsubmit="return confirm('Weet je het zeker?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                            </form>
                            @endcan
                        </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-muted">Nog geen routes. Maak er een aan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
