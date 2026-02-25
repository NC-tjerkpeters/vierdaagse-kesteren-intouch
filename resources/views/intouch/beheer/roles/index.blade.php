@extends('intouch.layout')

@section('title', 'Rollen beheren')

@section('content')
<h1 class="mb-4">Rollen beheren</h1>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rollen</span>
        <a href="{{ route('intouch.beheer.roles.create') }}" class="btn btn-light btn-sm">Nieuwe rol</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Naam</th>
                        <th>Slug</th>
                        <th>Rechten</th>
                        <th>Gebruikers</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td><code>{{ $role->slug }}</code></td>
                        <td>
                            @forelse($role->permissions as $perm)
                            <span class="badge bg-secondary">{{ $perm->name }}</span>
                            @empty
                            <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>{{ $role->users_count }}</td>
                        <td>
                            <a href="{{ route('intouch.beheer.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                            @if($role->users_count === 0)
                            <form method="post" action="{{ route('intouch.beheer.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Rol definitief verwijderen?');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<p class="mt-3">
    <a href="{{ route('intouch.beheer.users.index') }}" class="text-decoration-none">← Gebruikersbeheer</a>
</p>
@endsection
