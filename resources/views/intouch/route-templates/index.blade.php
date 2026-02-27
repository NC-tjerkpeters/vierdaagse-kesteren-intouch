@extends('intouch.layout')

@section('title', 'Routebibliotheek')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Routebibliotheek</h1>
        <p class="text-muted small mb-0">Centraal overzicht van alle beschikbare routes. Voeg toe aan een editie via Werkgroep → Routes.</p>
    </div>
    <a href="{{ route('intouch.route-templates.create') }}" class="btn btn-vierdaagse">Nieuwe route</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Afstand</th>
                    <th>Titel</th>
                    <th>Punten</th>
                    <th>Word</th>
                    <th>PDF</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $t)
                <tr>
                    <td>{{ $t->distance->name ?? '-' }}</td>
                    <td>{{ $t->title ?: '-' }}</td>
                    <td>{{ $t->points->count() }}</td>
                    <td>
                        @if($t->word_path)
                            <a href="{{ route('intouch.route-templates.word', $t) }}" download class="badge bg-primary text-decoration-none">Download</a>
                        @else
                            <span class="badge bg-secondary">Nee</span>
                        @endif
                    </td>
                    <td>
                        @if($t->pdf_path)
                            <a href="{{ route('intouch.route-templates.pdf', $t) }}" target="_blank" class="badge bg-success text-decoration-none">Bekijken</a>
                        @else
                            <span class="badge bg-secondary">Nee</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('intouch.route-templates.edit', $t) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                        <form method="post" action="{{ route('intouch.route-templates.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Route uit bibliotheek verwijderen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-muted">Nog geen routes in de bibliotheek. Maak er een aan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
