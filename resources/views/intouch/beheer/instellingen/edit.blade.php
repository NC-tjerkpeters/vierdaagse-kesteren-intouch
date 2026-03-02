@extends('intouch.layout')

@section('title', 'Systeeminstellingen')

@section('content')
<h1 class="mb-4">Systeeminstellingen</h1>

<form method="post" action="{{ route('intouch.beheer.instellingen.update') }}">
    @csrf
    @method('put')

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Sponsors</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="sponsors_doelbedrag" class="form-label">Doelbedrag sponsors (€)</label>
                        <input type="number" name="sponsors_doelbedrag" id="sponsors_doelbedrag" class="form-control"
                            step="0.01" min="0" value="{{ old('sponsors_doelbedrag', $sponsorsDoelbedrag) }}"
                            placeholder="1850">
                        <small class="text-muted">Gebruikt voor de voortgangsbalk op het dashboard</small>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="sponsors_privacy_consent_required" value="0">
                        <input type="checkbox" class="form-check-input" name="sponsors_privacy_consent_required"
                            id="sponsors_privacy_consent_required" value="1"
                            @checked(old('sponsors_privacy_consent_required', $sponsorsPrivacyConsentRequired ?? true))>
                        <label class="form-check-label" for="sponsors_privacy_consent_required">
                            AVG-privacycheckbox verplicht bij sponsoraanmelding
                        </label>
                        <small class="form-text text-muted d-block">Uitgeschakeld: het externe sponsorformulier hoeft geen privacy-checkbox te tonen (handig als u daar geen invloed op heeft).</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Algemeen</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="app_noodnummers" class="form-label">Noodnummers (op tickets)</label>
                        <input type="text" name="app_noodnummers" id="app_noodnummers" class="form-control"
                            value="{{ old('app_noodnummers', $noodnummers) }}"
                            placeholder="06 52 44 16 10, 06 40 89 37 40">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Scanner</div>
        <div class="card-body">
            <div class="mb-4">
                <label for="scanner_min_minutes" class="form-label">Minimum minuten tussen twee scans</label>
                <input type="number" name="scanner_min_minutes" id="scanner_min_minutes" class="form-control"
                    min="1" max="60" value="{{ old('scanner_min_minutes', $scannerMinMinutes) }}"
                    style="width: 100px">
            </div>
            <h6 class="mb-2">Namen scanpunten</h6>
            <div class="row g-2">
                @for ($i = 1; $i <= 13; $i++)
                    <div class="col-md-4 col-lg-3">
                        <label for="scanner_point_{{ $i }}" class="form-label small">Punt {{ $i }}</label>
                        <input type="text" name="scanner_point_{{ $i }}" id="scanner_point_{{ $i }}"
                            class="form-control form-control-sm"
                            value="{{ old("scanner_point_{$i}", $scannerPointNames[$i] ?? "Punt {$i}") }}">
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Mollie transactiekosten</div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Schatting per betaalmethode. Bron: <a href="https://www.mollie.com/pricing" target="_blank" rel="noopener">mollie.com/pricing</a> – controleer regelmatig op wijzigingen.
            </p>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Betaalmethode</th>
                            <th>Percentage (%)</th>
                            <th>Vast bedrag (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mollieFeeMethods as $method)
                            @if ($method !== 'default')
                                @php
                                    $fee = $mollieFees[$method] ?? ['percentage' => 0, 'fixed' => 0];
                                    $pct = $fee['percentage'] ?? 0;
                                    $fix = $fee['fixed'] ?? 0;
                                @endphp
                                <tr>
                                    <td>{{ ucfirst($method) }}</td>
                                    <td>
                                        <input type="number" name="mollie_fee_{{ $method }}_percentage"
                                            class="form-control form-control-sm" step="0.01" min="0" max="100"
                                            value="{{ old("mollie_fee_{$method}_percentage", $pct) }}"
                                            style="width: 80px">
                                    </td>
                                    <td>
                                        <input type="number" name="mollie_fee_{{ $method }}_fixed"
                                            class="form-control form-control-sm" step="0.01" min="0"
                                            value="{{ old("mollie_fee_{$method}_fixed", $fix) }}"
                                            style="width: 80px">
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="table-light">
                            <td><strong>Default</strong> (onbekende methode)</td>
                            @php
                                $def = $mollieFees['default'] ?? ['percentage' => 1.8, 'fixed' => 0.25];
                            @endphp
                            <td>
                                <input type="number" name="mollie_fee_default_percentage"
                                    class="form-control form-control-sm" step="0.01" min="0" max="100"
                                    value="{{ old('mollie_fee_default_percentage', $def['percentage'] ?? 1.8) }}"
                                    style="width: 80px">
                            </td>
                            <td>
                                <input type="number" name="mollie_fee_default_fixed"
                                    class="form-control form-control-sm" step="0.01" min="0"
                                    value="{{ old('mollie_fee_default_fixed', $def['fixed'] ?? 0.25) }}"
                                    style="width: 80px">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
    <a href="{{ route('intouch.dashboard') }}" class="btn btn-outline-secondary">Annuleren</a>
</form>
@endsection
