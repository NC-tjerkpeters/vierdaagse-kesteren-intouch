@extends('intouch.layout')

@section('title', 'Sponsor bewerken')

@section('content')
<h1 class="mb-4">Sponsor bewerken</h1>

<div class="card" style="max-width: 540px">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.sponsors.update', $sponsor) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="bedrijfsnaam" class="form-label">Bedrijfsnaam (optioneel)</label>
                <input type="text" id="bedrijfsnaam" name="bedrijfsnaam" class="form-control @error('bedrijfsnaam') is-invalid @enderror" value="{{ old('bedrijfsnaam', $sponsor->bedrijfsnaam) }}">
                @error('bedrijfsnaam')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="voornaam" class="form-label">Voornaam *</label>
                    <input type="text" id="voornaam" name="voornaam" class="form-control @error('voornaam') is-invalid @enderror" value="{{ old('voornaam', $sponsor->voornaam) }}" required>
                    @error('voornaam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="achternaam" class="form-label">Achternaam *</label>
                    <input type="text" id="achternaam" name="achternaam" class="form-control @error('achternaam') is-invalid @enderror" value="{{ old('achternaam', $sponsor->achternaam) }}" required>
                    @error('achternaam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="postcode" class="form-label">Postcode</label>
                    <input type="text" id="postcode" name="postcode" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode', $sponsor->postcode) }}">
                    @error('postcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="huisnummer" class="form-label">Huisnummer</label>
                    <input type="text" id="huisnummer" name="huisnummer" class="form-control @error('huisnummer') is-invalid @enderror" value="{{ old('huisnummer', $sponsor->huisnummer) }}">
                    @error('huisnummer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefoonnummer" class="form-label">Telefoonnummer</label>
                    <input type="text" id="telefoonnummer" name="telefoonnummer" class="form-control @error('telefoonnummer') is-invalid @enderror" value="{{ old('telefoonnummer', $sponsor->telefoonnummer) }}">
                    @error('telefoonnummer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail *</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $sponsor->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="bedrag" class="form-label">Bedrag *</label>
                    <input type="number" id="bedrag" name="bedrag" step="0.01" min="0" class="form-control @error('bedrag') is-invalid @enderror" value="{{ old('bedrag', $sponsor->bedrag) }}" required>
                    @error('bedrag')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="betaalstatus" class="form-label">Betaalstatus</label>
                    <select id="betaalstatus" name="betaalstatus" class="form-select @error('betaalstatus') is-invalid @enderror">
                        <option value="paid" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'paid')>paid</option>
                        <option value="open" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'open')>open</option>
                        <option value="pending" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'pending')>pending</option>
                        <option value="authorized" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'authorized')>authorized</option>
                        <option value="failed" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'failed')>failed</option>
                        <option value="canceled" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'canceled')>canceled</option>
                        <option value="expired" @selected(old('betaalstatus', $sponsor->betaalstatus) === 'expired')>expired</option>
                    </select>
                    @error('betaalstatus')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
                <a href="{{ route('intouch.sponsors.index') }}" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
    </div>
</div>
@endsection
