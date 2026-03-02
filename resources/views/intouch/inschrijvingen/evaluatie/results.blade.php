@extends('intouch.layout')

@section('title', 'Resultaten: ' . $evaluation->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('intouch.registrations.evaluatie.show', $evaluation) }}" class="btn btn-outline-secondary">← Terug</a>
</div>

<h1 class="mb-4">Resultaten: {{ $evaluation->name }}</h1>

<p class="text-muted mb-4">
    {{ $responses->count() }} van {{ $evaluation->getTargetRegistrationsQuery()->count() }} deelnemers hebben gereageerd
    @if($evaluation->getTargetRegistrationsQuery()->count() > 0)
        ({{ round($responses->count() / $evaluation->getTargetRegistrationsQuery()->count() * 100) }}%)
    @endif
    <a href="{{ route('intouch.registrations.evaluatie.export', $evaluation) }}" class="btn btn-sm btn-outline-secondary ms-2">Export CSV</a>
</p>

@foreach($evaluation->questions as $q)
    @php $agg = $aggregates[$q->id] ?? []; @endphp
    <div class="card mb-4">
        <div class="card-header">{{ $q->question_text }}</div>
        <div class="card-body">
            @if(($agg['count'] ?? 0) === 0)
                <p class="text-muted mb-0">Nog geen antwoorden.</p>
            @elseif(($agg['type'] ?? '') === 'nps')
                <p class="mb-2">Gemiddeld: <strong>{{ $agg['average'] ?? '-' }}</strong> · Promoters (9–10): {{ $agg['promoters'] ?? 0 }} · Passives (7–8): {{ $agg['passives'] ?? 0 }} · Detractors (0–6): {{ $agg['detractors'] ?? 0 }}</p>
                <div class="d-flex gap-1 align-items-end mb-0" style="height: 60px;">
                    @foreach(range(0, 10) as $i)
                        @php $h = (($agg['distribution'][$i] ?? 0) / max(1, $agg['count'])) * 50; @endphp
                        <div class="flex-fill bg-primary opacity-75 rounded-top" style="height: {{ $h }}px;" title="{{ $i }}: {{ $agg['distribution'][$i] ?? 0 }}"></div>
                    @endforeach
                </div>
                <small class="text-muted">0 1 2 3 4 5 6 7 8 9 10</small>
            @elseif(($agg['type'] ?? '') === 'rating')
                <p class="mb-2">Gemiddeld: <strong>{{ $agg['average'] ?? '-' }}</strong> ★</p>
                @foreach(range(1, 5) as $i)
                    <span class="me-2">★{{ $i }}: {{ $agg['distribution'][$i] ?? 0 }}</span>
                @endforeach
            @elseif(($agg['type'] ?? '') === 'choice')
                <ul class="list-unstyled mb-0">
                    @foreach($agg['counts'] ?? [] as $opt => $cnt)
                    <li>{{ e($opt) }}: {{ $cnt }}</li>
                    @endforeach
                </ul>
            @else
                <ul class="list-unstyled mb-0">
                    @foreach(array_slice($agg['values'] ?? [], 0, 10) as $v)
                    <li class="border-bottom pb-1 mb-1">"{{ e($v) }}"</li>
                    @endforeach
                    @if(count($agg['values'] ?? []) > 10)
                    <li class="text-muted">... en {{ count($agg['values']) - 10 }} meer (zie export)</li>
                    @endif
                </ul>
            @endif
        </div>
    </div>
@endforeach
@endsection
