@extends('intouch.layout')

@section('title', 'Nieuwe gebruiker')

@section('content')
<h1 class="mb-4">Nieuwe gebruiker</h1>

<div class="card">
    <div class="card-header">Gebruiker aanmaken</div>
    <div class="card-body">
        <form method="post" action="{{ route('intouch.beheer.users.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Naam</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Wachtwoord</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Wachtwoord bevestigen</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
            </div>
            @if(auth()->user()->canManageRoles())
                <div class="mb-4">
                    <label class="form-label">Rollen</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <button type="submit" class="btn btn-vierdaagse">Gebruiker aanmaken</button>
            <a href="{{ route('intouch.beheer.users.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
