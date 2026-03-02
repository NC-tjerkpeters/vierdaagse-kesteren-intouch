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
            </div>
        </div>
    </div>
</div>

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
@endsection
