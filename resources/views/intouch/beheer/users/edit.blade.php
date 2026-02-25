@extends('intouch.layout')

@section('title', 'Gebruiker bewerken')

@section('content')
<h1 class="mb-4">Gebruiker bewerken</h1>

<div class="card">
    <div class="card-header">{{ $user->name }}</div>
    <div class="card-body">
        <form method="post" action="{{ route('intouch.beheer.users.update', $user) }}">
            @csrf
            @method('put')
            <div class="mb-3">
                <label class="form-label">Naam</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Nieuw wachtwoord <span class="text-muted">(laat leeg om niet te wijzigen)</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Wachtwoord bevestigen</label>
                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
            </div>
            @if(auth()->user()->canManageRoles())
                <div class="mb-4">
                    <label class="form-label">Rollen</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                                    {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
            <a href="{{ route('intouch.beheer.users.index') }}" class="btn btn-outline-secondary">Terug</a>
        </form>
    </div>
</div>
@endsection
