@extends('intouch.layout')

@section('title', 'Afstanden')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Afstanden</h1>
    @can('afstanden_create')
    <a href="{{ route('intouch.afstanden.create') }}" class="btn btn-primary">Nieuwe afstand</a>
    @endcan
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Volgorde</th>
                    <th>Naam</th>
                    <th>Km</th>
                    <th>Prijs</th>
                    <th>Loopt op</th>
                    <th>Actief</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($distances as $d)
                    <tr>
                        <td>{{ $d->sort_order }}</td>
                        <td>{{ $d->name }}</td>
                        <td>{{ $d->kilometers }}</td>
                        <td>€ {{ number_format($d->price, 2, ',', '.') }}</td>
                        <td>
                            @if($d->event_day_sort_orders === null || $d->event_day_sort_orders === [])
                                Alle avonden
                            @else
                                @php
                                    $days = \App\Models\EventDay::query()->orderBy('sort_order')->get()->keyBy('sort_order');
                                @endphp
                                {{ collect($d->event_day_sort_orders)->map(fn($n) => $days->get($n)?->name ?? 'Dag '.$n)->join(', ') }}
                            @endif
                        </td>
                        <td>{{ $d->is_active ? 'Ja' : 'Nee' }}</td>
                        <td class="text-end">
                            @can('afstanden_edit')
                            <a href="{{ route('intouch.afstanden.edit', $d) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                            @endcan
                            @can('afstanden_delete')
                            <form method="post" action="{{ route('intouch.afstanden.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Weet je het zeker?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-muted">Nog geen afstanden. Maak er een aan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
