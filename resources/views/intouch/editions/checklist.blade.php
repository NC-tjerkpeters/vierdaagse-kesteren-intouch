@extends('intouch.layout')

@section('title', 'Checklist – ' . $edition->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('intouch.beheer.editions.index') }}" class="text-muted text-decoration-none small">← Edities</a>
        <h1 class="mb-1">Checklist – {{ $edition->name }}</h1>
        <p class="text-muted small mb-0">{{ $edition->start_date->format('Y') }} – Werkgroep af te vinken</p>
    </div>
</div>

@if($edition->checklistItems->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-4">Deze editie heeft nog geen checklist-items. Laad de standaardlijst of voeg zelf items toe.</p>
            <form method="post" action="{{ route('intouch.beheer.editions.checklist.init', $edition) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-vierdaagse">Laad standaard checklist</button>
            </form>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('intouch.beheer.editions.checklist.update', $edition) }}">
                @csrf
                @method('put')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;">Klaar</th>
                                <th>Punt</th>
                                <th>Notitie / laatste stand</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($edition->checklistItems as $item)
                                <tr class="{{ $item->is_done ? 'table-success' : '' }}">
                                    <td>
                                        <input type="hidden" name="done_{{ $item->id }}" value="0">
                                        <input type="checkbox" class="form-check-input" name="done_{{ $item->id }}" value="1"
                                            id="done_{{ $item->id }}" {{ $item->is_done ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <label for="done_{{ $item->id }}" class="mb-0 {{ $item->is_done ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $item->title }}
                                        </label>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="note_{{ $item->id }}"
                                            value="{{ old("note_{$item->id}", $item->note) }}"
                                            placeholder="Korte notitie voor de werkgroep…">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-vierdaagse">Opslaan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Item toevoegen</div>
        <div class="card-body">
            <form method="post" action="{{ route('intouch.beheer.editions.checklist.add-item', $edition) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="title" class="form-control" placeholder="Nieuw punt…" required>
                <button type="submit" class="btn btn-outline-secondary">Toevoegen</button>
            </form>
        </div>
    </div>
@endif

<p class="text-muted small mt-4">
    Alle werkgroepleden met toegang tot Edities zien deze checklist en kunnen punten afvinken of notities toevoegen.
</p>
@endsection
