@extends('intouch.layout')

@section('title', $evaluation->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('intouch.registrations.evaluatie.index') }}" class="btn btn-outline-secondary">← Terug</a>
</div>

<h1 class="mb-4">{{ $evaluation->name }}</h1>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Doelgroep</h6>
                <p class="mb-0">
                    @if($evaluation->target === 'all_paid') Alle betaalde deelnemers
                    @elseif($evaluation->target === 'all_finished') Alleen deelnemers die alle 4 avonden hebben voltooid
                    @else Alleen deelnemers die een medaille willen
                    @endif
                </p>
                <p class="mb-0 mt-2"><strong>{{ $evaluation->responses()->count() }}</strong> / {{ $targetCount }} reacties</p>
                @if($evaluation->sent_at)
                    <p class="mb-0 text-muted small">Verstuurd op {{ $evaluation->sent_at->format('d-m-Y H:i') }}</p>
                @endif
                @if($evaluation->closes_at)
                    <p class="mb-0 text-muted small mt-1">Formulier sluit op {{ $evaluation->closes_at->format('d-m-Y H:i') }}</p>
                @endif
                @if($evaluation->reminder_days)
                    <p class="mb-0 text-muted small">Herinnering na {{ $evaluation->reminder_days }} dag(en)</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($evaluation->isSent() && $evaluation->invitations_total > 0 && $evaluation->invitations_sent_count < $evaluation->invitations_total)
<div class="alert alert-info mb-4" id="send-progress" role="status">
    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
    <span id="send-progress-text">Verstuurd: <strong>{{ $evaluation->invitations_sent_count }}</strong> / {{ $evaluation->invitations_total }} uitnodiging(en)</span>
</div>
@endif

<div class="d-flex gap-2 flex-wrap">
    @if(!$evaluation->isSent())
        @can('evaluatie_manage')
        <a href="{{ route('intouch.registrations.evaluatie.edit', $evaluation) }}" class="btn btn-outline-primary">Bewerken</a>
        @endcan
        @can('evaluatie_send')
        @if($evaluation->questions->count() > 0 && $targetCount > 0)
        <form method="post" action="{{ route('intouch.registrations.evaluatie.send', $evaluation) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-vierdaagse" onclick="return confirm('Verstuur uitnodigingen naar {{ $targetCount }} deelnemer(s)?')">
                Versturen naar {{ $targetCount }} deelnemer(s)
            </button>
        </form>
        @else
        <span class="btn btn-secondary disabled">Voeg vragen toe en zorg dat er deelnemers in de doelgroep zijn om te versturen</span>
        @endif
        @endcan
    @endif
    @if($evaluation->responses()->count() > 0)
    <a href="{{ route('intouch.registrations.evaluatie.results', $evaluation) }}" class="btn btn-success">Resultaten bekijken</a>
    <a href="{{ route('intouch.registrations.evaluatie.export', $evaluation) }}" class="btn btn-outline-secondary">Export CSV</a>
    @endif
    @can('evaluatie_manage')
    @if(!$evaluation->isSent() || $evaluation->responses()->count() === 0)
    <form method="post" action="{{ route('intouch.registrations.evaluatie.destroy', $evaluation) }}" class="d-inline" onsubmit="return confirm('Evaluatie verwijderen?')">
        @csrf
        @method('delete')
        <button type="submit" class="btn btn-outline-danger">Verwijderen</button>
    </form>
    @endif
    @endcan
</div>

@if($evaluation->isSent() && $evaluation->invitations_total > 0)
@push('scripts')
<script>
(function() {
    var progressEl = document.getElementById('send-progress');
    if (!progressEl) return;
    var total = {{ (int) $evaluation->invitations_total }};
    var statusUrl = '{{ route('intouch.registrations.evaluatie.send-status', $evaluation) }}';

    function poll() {
        fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var textEl = document.getElementById('send-progress-text');
                if (textEl) textEl.innerHTML = 'Verstuurd: <strong>' + (data.sent || 0) + '</strong> / ' + (data.total || total) + ' uitnodiging(en)';
                if (data.sent >= data.total && data.total > 0) {
                    progressEl.innerHTML = '<span class="text-success">Alle uitnodigingen zijn verstuurd.</span>';
                    return;
                }
                setTimeout(poll, 3000);
            })
            .catch(function() { setTimeout(poll, 5000); });
    }
    setTimeout(poll, 2000);
})();
</script>
@endpush
@endif
@endsection
