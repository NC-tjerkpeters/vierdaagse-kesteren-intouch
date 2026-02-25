@extends('intouch.layout')

@section('title', 'Gebruikersbeheer')

@section('content')
<h1 class="mb-4">Gebruikersbeheer</h1>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Gebruikers</span>
        <a href="{{ route('intouch.beheer.users.create') }}" class="btn btn-light btn-sm">Nieuwe gebruiker</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Rollen</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse($user->roles as $role)
                            <span class="badge bg-secondary">{{ $role->name }}</span>
                            @empty
                            <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            <a href="{{ route('intouch.beheer.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@can('manageRoles')
<p class="mt-3">
    <a href="{{ route('intouch.beheer.roles.index') }}" class="text-decoration-none">Rollen beheren →</a>
</p>
@endcan
@endsection
