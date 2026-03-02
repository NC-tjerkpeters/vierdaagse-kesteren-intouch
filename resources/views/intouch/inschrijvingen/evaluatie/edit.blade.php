@extends('intouch.layout')

@section('title', 'Evaluatie bewerken')

@section('content')
<div class="mb-4">
    <a href="{{ route('intouch.registrations.evaluatie.show', $evaluation) }}" class="btn btn-outline-secondary">← Terug</a>
</div>

<h1 class="mb-4">Evaluatie bewerken</h1>

@include('intouch.inschrijvingen.evaluatie._form', [
    'evaluation' => $evaluation,
    'edition' => $evaluation->edition,
    'action' => route('intouch.registrations.evaluatie.update', $evaluation),
    'method' => 'put',
])
@endsection
