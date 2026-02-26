@extends('intouch.layout')

@section('title', 'Mijn profiel')

@section('content')
<div class="card">
    <div class="card-header">Mijn profiel</div>
    <div class="card-body">
        <form method="post" action="{{ route('intouch.instellingen.update') }}">
            @csrf
            @method('put')
            <div class="mb-3">
                <label class="form-label">Naam</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Nieuw wachtwoord (laat leeg om niet te wijzigen)</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label">Nieuw wachtwoord bevestigen</label>
                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
            <a href="{{ route('intouch.dashboard') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
