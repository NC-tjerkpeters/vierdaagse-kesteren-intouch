@extends('routes.layout')

@section('title', 'Routes')

@section('content')
@if(!$edition)
<div class="alert alert-info">Er is momenteel geen actieve editie. De routes worden binnenkort weer beschikbaar.</div>
@elseif($walkRoutes->isEmpty())
<div class="alert alert-info">Er zijn nog geen routes beschikbaar voor {{ $edition->name }}.</div>
@else
<h1 class="mb-4">Wandelroutes {{ $edition->name }}</h1>
<p class="text-muted mb-4">Kies een route om de controlepunten te bekijken, af te strepen tijdens het wandelen en de PDF te downloaden.</p>
<div class="row g-3">
    @foreach($walkRoutes as $route)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    {{ $route->distance->name ?? 'Route' }}
                    @if($route->title)
                        <br><small class="text-muted fw-normal">{{ $route->title }}</small>
                    @endif
                </h5>
                @if($route->description)
                    <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($route->description, 100) }}</p>
                @endif
                <p class="card-text small mb-2">
                    {{ $route->points->count() }} controlepunten
                    @if($route->pdf_path)
                        · PDF beschikbaar
                    @endif
                </p>
                <a href="{{ route('routes.show', $route) }}" class="btn btn-vierdaagse">Route bekijken</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
