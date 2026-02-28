@extends('intouch.layout')

@section('title', 'E-mailtemplates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">E-mailtemplates</h1>
    <div>
        <a href="{{ route('intouch.registrations.communicatie') }}" class="btn btn-outline-secondary">← Communicatie</a>
        <a href="{{ route('intouch.registrations.communicatie.templates.create') }}" class="btn btn-vierdaagse">Nieuwe template</a>
    </div>
</div>

<p class="text-muted mb-4">
    Beheer de e-mailtemplates voor communicatie naar deelnemers. Plaatshouders: @{{voornaam}}, @{{achternaam}}, @{{afstand}}, @{{edition_name}}, @{{start_datum}}, @{{eind_datum}}, @{{inschrijf_url}}, @{{routes_url}}
</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Onderwerp</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $t)
                    <tr>
                        <td>{{ $t->name }}</td>
                        <td class="text-muted small">{{ Str::limit($t->subject, 60) }}</td>
                        <td>
                            <a href="{{ route('intouch.registrations.communicatie.templates.edit', $t) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                            <form method="post" action="{{ route('intouch.registrations.communicatie.templates.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Template verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-muted">Geen templates. <a href="{{ route('intouch.registrations.communicatie.templates.create') }}">Maak er een aan</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
