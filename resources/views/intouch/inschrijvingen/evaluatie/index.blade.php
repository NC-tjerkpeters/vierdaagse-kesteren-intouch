@extends('intouch.layout')

@section('title', 'Evaluatie')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Evaluatie</h1>
    <div>
        @can('evaluatie_send')
        <a href="{{ route('intouch.registrations.evaluatie.create') }}" class="btn btn-vierdaagse">Nieuwe evaluatie</a>
        @endcan
        <a href="{{ route('intouch.registrations.index') }}" class="btn btn-outline-secondary ms-1">← Inschrijvingen</a>
    </div>
</div>

<p class="text-muted mb-4">
    Beheer evaluaties voor {{ $edition->name }}. Verstuur een vragenlijst naar deelnemers en bekijk de resultaten.
</p>

@forelse($evaluations as $evaluation)
    @php $targetCount = $targetCounts[$evaluation->id] ?? 0; @endphp
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ $evaluation->name }}</h5>
                <small class="text-muted">
                    {{ $evaluation->responses_count }} / {{ $targetCount }} reacties
                    @if($targetCount > 0)
                        ({{ round($evaluation->responses_count / $targetCount * 100) }}%)
                    @endif
                    @if($evaluation->sent_at)
                        · Verstuurd {{ $evaluation->sent_at->format('d-m-Y H:i') }}
                    @else
                        · Concept
                    @endif
                    @if($evaluation->isClosed())
                        · Afgesloten
                    @endif
                </small>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('intouch.registrations.evaluatie.show', $evaluation) }}" class="btn btn-sm btn-outline-primary">Details</a>
                @if($evaluation->responses_count > 0)
                <a href="{{ route('intouch.registrations.evaluatie.results', $evaluation) }}" class="btn btn-sm btn-outline-success">Resultaten</a>
                <a href="{{ route('intouch.registrations.evaluatie.export', $evaluation) }}" class="btn btn-sm btn-outline-secondary">Export CSV</a>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <p class="mb-0">Nog geen evaluaties. Maak een evaluatie aan en verstuur deze naar deelnemers.</p>
            @can('evaluatie_send')
            <a href="{{ route('intouch.registrations.evaluatie.create') }}" class="btn btn-vierdaagse mt-3">Nieuwe evaluatie aanmaken</a>
            @endcan
        </div>
    </div>
@endforelse
@endsection
