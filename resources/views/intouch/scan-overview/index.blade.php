@extends('intouch.layout')

@section('title', 'Loopoverzicht')

@section('content')
<h1 class="mb-4">Loopoverzicht</h1>
<p class="text-muted mb-3">Totaal aantal deelnemers: <strong>{{ $totalParticipants ?? 0 }}</strong></p>

@if(!$currentDay)
    <div class="alert alert-warning mb-4">
        <strong>Geen actieve avond gekozen.</strong> Kies hieronder welke avond nu loopt; anders werkt de scanner niet goed en kloppen de aantallen niet.
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Huidige avond</h2>
        <p class="text-muted mb-3">
            Op de scanner start iedereen aan het begin van elke avond op hetzelfde punt. Kies hier welke avond nu loopt.
        </p>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="me-2">Huidige avond:</span>
            @foreach($eventDays as $day)
                <form method="post" action="{{ route('intouch.scan-overview.set-current-day') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="event_day_id" value="{{ $day->id }}">
                    @if($currentDay && $currentDay->id === $day->id)
                        <button type="button" class="btn btn-primary" disabled>{{ $day->name }} (actief)</button>
                    @else
                        <button type="submit" class="btn btn-outline-primary">{{ $day->name }} als start</button>
                    @endif
                </form>
            @endforeach
        </div>
    </div>
</div>

<p class="text-muted small mb-3">
    Per avond: aantal deelnemers per afstand bij <strong>start</strong>, <strong>post</strong> en <strong>finish</strong>. Onder „Niet bij finish” staan deelnemers die je kunt nabellen.
</p>

@foreach($eventDays as $day)
    @php
        $data = $overview[$day->id] ?? null;
        $points = $data['points'] ?? ['start' => 0, 'post' => 0, 'finish' => 0];
    @endphp
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">{{ $day->name }}</h2>
            @if($currentDay && $currentDay->id === $day->id)
                <span class="badge bg-success">Huidige avond</span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sm align-middle">
                <thead>
                    <tr>
                        <th>Afstand</th>
                        <th class="text-center">Start (punt {{ $points['start'] }})</th>
                        <th class="text-center">Post (punt {{ $points['post'] }})</th>
                        <th class="text-center">Finish (punt {{ $points['finish'] }})</th>
                        <th>Niet bij finish</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($distances as $distance)
                        @if(!isset($data['by_distance'][$distance->id]))
                            @continue
                        @endif
                        @php
                            $row = $data['by_distance'][$distance->id];
                            $missing = $row['missing'] ?? collect();
                            $isFutureDay = $currentDay && (int) $day->sort_order > (int) $currentDay->sort_order;
                        @endphp
                        <tr>
                            <td>{{ $distance->name }}</td>
                            <td class="text-center">{{ $row['start'] }}</td>
                            <td class="text-center">{{ $row['post'] }}</td>
                            <td class="text-center">{{ $row['finish'] }}</td>
                            <td>
                                @if($isFutureDay || $missing->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    <span class="text-danger fw-bold">{{ $missing->count() }}</span>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="collapse" data-bs-target="#miss-{{ $day->id }}-{{ $distance->id }}" aria-expanded="false">
                                        Toon namen
                                    </button>
                                    <div class="collapse mt-1 small" id="miss-{{ $day->id }}-{{ $distance->id }}">
                                        <ul class="list-unstyled mb-0">
                                            @foreach($missing as $reg)
                                                <li>
                                                    {{ $reg->first_name }} {{ $reg->last_name }}
                                                    @if($reg->phone_number)
                                                        — <a href="tel:{{ $reg->phone_number }}">{{ $reg->phone_number }}</a>
                                                    @else
                                                        <span class="text-muted">(geen telefoon)</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @php
                        $dayTotals = ['start' => 0, 'post' => 0, 'finish' => 0, 'missing' => 0];
                        foreach ($data['by_distance'] ?? [] as $row) {
                            $dayTotals['start'] += $row['start'] ?? 0;
                            $dayTotals['post'] += $row['post'] ?? 0;
                            $dayTotals['finish'] += $row['finish'] ?? 0;
                            $dayTotals['missing'] += isset($row['missing']) ? $row['missing']->count() : 0;
                        }
                    @endphp
                    <tr class="table-secondary fw-bold">
                        <td>Totaal</td>
                        <td class="text-center">{{ $dayTotals['start'] }}</td>
                        <td class="text-center">{{ $dayTotals['post'] }}</td>
                        <td class="text-center">{{ $dayTotals['finish'] }}</td>
                        <td>
                            @if(($currentDay && (int) $day->sort_order > (int) $currentDay->sort_order) || $dayTotals['missing'] === 0)
                                <span class="text-muted">—</span>
                            @else
                                <span class="text-danger fw-bold">{{ $dayTotals['missing'] }}</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
