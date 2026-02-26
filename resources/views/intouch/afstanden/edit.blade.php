@extends('intouch.layout')

@section('title', 'Afstand bewerken')

@section('content')
<h1 class="mb-4">Afstand bewerken</h1>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.beheer.afstanden.update', $distance) }}">
            @csrf
            @method('PUT')
            @include('intouch.afstanden._form', ['distance' => $distance])
            <button type="submit" class="btn btn-primary">Opslaan</button>
            <a href="{{ route('intouch.beheer.afstanden.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
