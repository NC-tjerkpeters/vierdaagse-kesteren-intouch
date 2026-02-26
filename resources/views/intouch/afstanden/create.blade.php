@extends('intouch.layout')

@section('title', 'Afstand toevoegen')

@section('content')
<h1 class="mb-4">Afstand toevoegen</h1>

<div class="card">
    <div class="card-body">
        <form method="post" action="{{ route('intouch.beheer.afstanden.store') }}">
            @csrf
            @include('intouch.afstanden._form')
            <button type="submit" class="btn btn-primary">Opslaan</button>
            <a href="{{ route('intouch.beheer.afstanden.index') }}" class="btn btn-outline-secondary">Annuleren</a>
        </form>
    </div>
</div>
@endsection
