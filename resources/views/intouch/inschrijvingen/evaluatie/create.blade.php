@extends('intouch.layout')

@section('title', 'Evaluatie aanmaken')

@section('content')
<div class="mb-4">
    <a href="{{ route('intouch.registrations.evaluatie.index') }}" class="btn btn-outline-secondary">← Terug</a>
</div>

<h1 class="mb-4">Evaluatie aanmaken</h1>

@if(empty($initialQuestions ?? []))
<p class="text-muted mb-2">
    <a href="{{ route('intouch.registrations.evaluatie.create', ['template' => 1]) }}">Starten met standaardvragen</a> (NPS, tevredenheid, open vragen)
</p>
@endif

@include('intouch.inschrijvingen.evaluatie._form', [
    'evaluation' => null,
    'edition' => $edition,
    'action' => route('intouch.registrations.evaluatie.store'),
    'method' => 'post',
    'initialQuestions' => $initialQuestions ?? [],
])
@endsection
