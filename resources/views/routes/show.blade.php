@extends('routes.layout')

@section('title', $walkRoute->title ?: $walkRoute->distance->name ?? 'Route')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('routes.index') }}" class="text-muted text-decoration-none small">← Alle routes</a>
        <h1 class="mb-1">{{ $walkRoute->title ?: $walkRoute->distance->name ?? 'Route' }}</h1>
        @if($walkRoute->description)
            <p class="text-muted mb-0">{{ $walkRoute->description }}</p>
        @endif
    </div>
    @if($walkRoute->pdf_path)
        <a href="{{ route('routes.pdf', $walkRoute) }}" target="_blank" class="btn btn-vierdaagse">PDF downloaden</a>
    @endif
</div>

<div class="card">
    <div class="card-header">Controlepunten</div>
    <div class="card-body">
        <p class="text-muted small mb-3">Streep tijdens het wandelen de punten af. Je voortgang wordt lokaal opgeslagen.</p>
        <ul class="list-group list-group-flush" id="points-list">
            @foreach($walkRoute->points as $point)
                <li class="list-group-item d-flex align-items-center point-item" data-point-id="{{ $point->id }}">
                    <input type="checkbox" class="form-check-input me-3 point-checkbox" id="point-{{ $point->id }}" data-point-id="{{ $point->id }}">
                    <label class="form-check-label flex-grow-1 point-label" for="point-{{ $point->id }}">{{ $point->name }}</label>
                </li>
            @endforeach
        </ul>
        @if($walkRoute->points->isEmpty())
            <p class="text-muted mb-0">Geen controlepunten ingesteld voor deze route.</p>
        @endif
    </div>
</div>

@if($walkRoute->points->isNotEmpty())
<script>
(function() {
    const routeId = {{ $walkRoute->id }};
    const storageKey = 'route-' + routeId + '-checked';

    function loadChecked() {
        try {
            const data = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(data) ? data : [];
        } catch (e) {
            return [];
        }
    }

    function saveChecked(ids) {
        localStorage.setItem(storageKey, JSON.stringify(ids));
    }

    const checked = loadChecked();
    document.querySelectorAll('.point-checkbox').forEach(function(cb) {
        const id = parseInt(cb.dataset.pointId, 10);
        if (checked.includes(id)) {
            cb.checked = true;
            cb.closest('.point-item').querySelector('.point-label').classList.add('point-checked');
        }
        cb.addEventListener('change', function() {
            const label = cb.closest('.point-item').querySelector('.point-label');
            let ids = loadChecked();
            if (cb.checked) {
                if (!ids.includes(id)) ids.push(id);
                label.classList.add('point-checked');
            } else {
                ids = ids.filter(function(x) { return x !== id; });
                label.classList.remove('point-checked');
            }
            saveChecked(ids);
        });
    });
})();
</script>
@endif
@endsection
