@extends('intouch.layout')

@section('title', 'Inschrijving #' . $registration->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Inschrijving #{{ $registration->id }}</h1>
    <a href="{{ route('intouch.registrations.index') }}" class="btn btn-outline-secondary">Terug naar overzicht</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-borderless mb-0">
            <tr>
                <th class="text-muted" style="width: 180px;">Voornaam</th>
                <td>{{ $registration->first_name }}</td>
            </tr>
            <tr>
                <th class="text-muted">Achternaam</th>
                <td>{{ $registration->last_name }}</td>
            </tr>
            <tr>
                <th class="text-muted">Postcode</th>
                <td>{{ $registration->postal_code }}</td>
            </tr>
            <tr>
                <th class="text-muted">Huisnummer</th>
                <td>{{ $registration->house_number }}</td>
            </tr>
            <tr>
                <th class="text-muted">Telefoon</th>
                <td>{{ $registration->phone_number }}</td>
            </tr>
            <tr>
                <th class="text-muted">E-mail</th>
                <td>{{ $registration->email }}</td>
            </tr>
            <tr>
                <th class="text-muted">Afstand</th>
                <td>{{ $registration->distance->name ?? '-' }}</td>
            </tr>
            <tr>
                <th class="text-muted">Medaille</th>
                <td>{{ $registration->wants_medal ? 'Ja' : 'Nee' }}</td>
            </tr>
            @if($registration->wants_medal && $registration->medal_number)
                <tr>
                    <th class="text-muted">Medaillenummer</th>
                    <td>{{ $registration->medal_number }}</td>
                </tr>
            @endif
            <tr>
                <th class="text-muted">Betaling</th>
                <td>
                    @if($registration->mollie_payment_status === 'paid')
                        <span class="badge bg-success">Betaald</span>
                    @else
                        <span class="badge bg-secondary">{{ $registration->mollie_payment_status }}</span>
                    @endif
                    @if($registration->mollie_payment_id)
                        <small class="text-muted">({{ $registration->mollie_payment_id }})</small>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="text-muted">Inschrijfdatum</th>
                <td>{{ $registration->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @if($registration->qr_code)
                <tr>
                    <th class="text-muted">QR-code</th>
                    <td><code class="small">{{ $registration->qr_code }}</code></td>
                </tr>
            @endif
        </table>
    </div>
</div>
@endsection
