@extends('intouch.layout')

@section('title', 'Herstelcodes')

@section('content')
<div class="card" style="max-width: 480px">
    <div class="card-header">Twee-factor authenticatie ingeschakeld</div>
    <div class="card-body">
        <div class="alert alert-success">Twee-factor authenticatie is succesvol ingesteld.</div>

        <p class="mb-3">Bewaar deze herstelcodes op een veilige plek. Je kunt ze gebruiken om in te loggen als je geen toegang hebt tot je authenticator-app.</p>

        <div class="bg-light p-3 rounded mb-4 font-monospace small">
            @foreach($recoveryCodes as $code)
                <div>{{ $code }}</div>
            @endforeach
        </div>

        <p class="text-muted small mb-4">Elke code kan maar één keer worden gebruikt. Nadat je een code hebt gebruikt, wordt deze verwijderd.</p>

        <a href="{{ route('intouch.instellingen.edit') }}" class="btn btn-vierdaagse">Terug naar Mijn profiel</a>
    </div>
</div>
@endsection
