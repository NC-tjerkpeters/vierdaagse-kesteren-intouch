@extends('intouch.layout')

@section('title', 'Nieuwe rol')

@section('content')
<h1 class="mb-4">Nieuwe rol</h1>

<div class="card">
    <div class="card-header">Rol aanmaken</div>
    <div class="card-body">
        <form method="post" action="{{ route('intouch.beheer.roles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Naam</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (kleine letters, cijfers, underscores)</label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" pattern="[a-z0-9_]+" required>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label">Rechten</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($permissions as $perm)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm-{{ $perm->id }}" {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm-{{ $perm->id }}">{{ $perm->name }}</label>
                    </div>
                    @endforeach
                </div>
                @error('permissions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-vierdaagse">Rol aanmaken</button>
            <a href="{{ route('intouch.beheer.roles.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>

<p class="mt-3">
    <a href="{{ route('intouch.beheer.roles.index') }}" class="text-decoration-none">← Terug naar rollen</a>
</p>
@endsection
