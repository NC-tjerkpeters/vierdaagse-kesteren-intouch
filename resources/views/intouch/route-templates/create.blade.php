@extends('intouch.layout')

@section('title', 'Route toevoegen aan bibliotheek')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('intouch.route-templates.index') }}" class="text-muted text-decoration-none small">← Routebibliotheek</a>
        <h1 class="mb-1">Nieuwe route in bibliotheek</h1>
        <p class="text-muted small mb-0">Voeg een route toe aan de centrale bibliotheek. Word-document = bron voor toekomstige wijzigingen, PDF = voor de voorkant.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.route-templates.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="distance_id" class="form-label">Afstand</label>
                <select id="distance_id" name="distance_id" class="form-select @error('distance_id') is-invalid @enderror" required>
                    <option value="">– Kies een afstand –</option>
                    @foreach($distances as $d)
                        <option value="{{ $d->id }}" @selected(old('distance_id') == $d->id)>{{ $d->name }} ({{ $d->kilometers }} km)</option>
                    @endforeach
                </select>
                @error('distance_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Titel (optioneel)</label>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Bijv. Route 5 km – Via Kesteren">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Beschrijving (optioneel)</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="word" class="form-label">Word-document (bron)</label>
                    <input type="file" id="word" name="word" class="form-control @error('word') is-invalid @enderror" accept=".doc,.docx">
                    <small class="text-muted">Optioneel. .doc/.docx, max 20 MB. Bewaar als bron om later de PDF te wijzigen.</small>
                    @error('word')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pdf" class="form-label">PDF (voorkant)</label>
                    <input type="file" id="pdf" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept=".pdf">
                    <small class="text-muted">Optioneel. Max 10 MB. Wordt gekopieerd bij toevoegen aan editie.</small>
                    @error('pdf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vierdaagse">Aanmaken</button>
                <a href="{{ route('intouch.route-templates.index') }}" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
    </div>
</div>
@endsection
