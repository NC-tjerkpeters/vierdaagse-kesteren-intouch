@extends('intouch.layout')

@section('title', 'Twee-factor authenticatie instellen')

@section('content')
<div class="card" style="max-width: 480px">
    <div class="card-header">Twee-factor authenticatie instellen</div>
    <div class="card-body">
        <p class="text-muted mb-3">Scan de QR-code met een authenticator-app en voer daarna de 6-cijferige code in.</p>
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="text-center mb-4">
            <img src="{{ $qrCodeUrl }}" alt="QR-code" width="200" height="200">
        </div>
        <p class="small text-muted mb-3">Kan je de QR-code niet scannen? Voer deze code handmatig in: <code>{{ $secret }}</code></p>
        <form method="post" action="{{ route('intouch.instellingen.two-factor.confirm') }}">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Verificatiecode (6 cijfers)</label>
                <input type="text" id="code" name="code" class="form-control" inputmode="numeric" maxlength="6" placeholder="000000" required>
            </div>
            <button type="submit" class="btn btn-vierdaagse">Bevestigen</button>
            <a href="{{ route('intouch.instellingen.edit') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
