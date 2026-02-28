@extends('intouch.layout')

@section('title', 'Vrijwilligersrooster')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Vrijwilligersrooster</h1>
    @can('vrijwilligers_manage')
    <a href="{{ route('intouch.volunteers.create') }}" class="btn btn-vierdaagse">Vrijwilliger toevoegen</a>
    @endcan
</div>

<p class="text-muted mb-4">Vrijwilligers en rooster voor {{ $edition->name }}.</p>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'lijst' ? 'active' : '' }}" href="{{ route('intouch.volunteers.index', ['tab' => 'lijst']) }}">Lijst</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'rooster' ? 'active' : '' }}" href="{{ route('intouch.volunteers.index', ['tab' => 'rooster']) }}">Rooster</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'verkeersregelaars' ? 'active' : '' }}" href="{{ route('intouch.volunteers.index', ['tab' => 'verkeersregelaars']) }}">Verkeersregelaars</a>
    </li>
</ul>

@if($tab === 'lijst')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>E-mail</th>
                    <th>Telefoon</th>
                    <th>Beschikbaar</th>
                    <th>Verkeer</th>
                    <th>Ingepland</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($volunteers as $v)
                <tr>
                    <td>{{ $v->name }}</td>
                    <td>{{ $v->email }}</td>
                    <td>{{ $v->phone ?? '–' }}</td>
                    <td>
                        @if($v->availabilities->isEmpty())
                        <span class="text-muted">–</span>
                        @else
                        {{ $v->availabilities->sortBy(fn($a) => $a->eventDay?->sort_order)->map(fn($a) => $a->eventDay?->name)->filter()->join(', ') }}
                        @endif
                    </td>
                    <td>@if($v->can_regulate_traffic)<span class="badge bg-success">Bevoegd</span>@else<span class="text-muted">–</span>@endif</td>
                    <td>{{ $v->slots_count }}x</td>
                    <td>
                        @can('vrijwilligers_manage')
                        <a href="{{ route('intouch.volunteers.edit', $v) }}" class="btn btn-sm btn-outline-primary">Bewerken</a>
                        <form method="post" action="{{ route('intouch.volunteers.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Vrijwilliger verwijderen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-muted">Nog geen vrijwilligers. @can('vrijwilligers_manage')<a href="{{ route('intouch.volunteers.create') }}">Voeg er een toe</a>.@endcan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@elseif($tab === 'rooster')
<p class="text-muted mb-3">Vrijwilligers die als verkeersregelaar op een route staan, kunnen niet tegelijk in het rooster op die dag.</p>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Dag</th>
                        @foreach($roles as $roleKey => $roleName)
                        <th>{{ $roleName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($eventDays as $day)
                    <tr>
                        <td class="fw-bold">{{ $day->name }}</td>
                        @foreach($roles as $roleKey => $roleName)
                        @php $slot = $slotsByDayRole[$day->id][$roleKey] ?? null; @endphp
                        <td>
                            @can('vrijwilligers_manage')
                            @php $slotConflict = $slot && in_array($day->id, $verkeersregelaarDaysByVolunteer[$slot->volunteer_id] ?? []); @endphp
                            @if($slotConflict)<span class="text-danger" title="Staat ook als verkeersregelaar op deze dag">⛔</span> @endif
                            <form method="post" action="{{ route('intouch.volunteers.assign-slot') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="event_day_id" value="{{ $day->id }}">
                                <input type="hidden" name="role" value="{{ $roleKey }}">
                                <select name="volunteer_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">– kies –</option>
                                    @foreach($volunteers as $v)
                                    @php
                                        $available = in_array($day->id, $availabilityByVolunteer[$v->id] ?? []);
                                        $isVerkeersregelaarDieDag = in_array($day->id, $verkeersregelaarDaysByVolunteer[$v->id] ?? []);
                                        $isCurrentSlot = $slot && $slot->volunteer_id === $v->id;
                                        $showOption = !$isVerkeersregelaarDieDag || $isCurrentSlot;
                                    @endphp
                                    @if($showOption)
                                    <option value="{{ $v->id }}" @selected($isCurrentSlot) @disabled($isVerkeersregelaarDieDag && !$isCurrentSlot)>
                                        {{ $v->name }}{{ !$available ? ' (niet beschikbaar)' : '' }}{{ $isVerkeersregelaarDieDag ? ' – verkeersregelaar die dag' : '' }}
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                            </form>
                            @else
                            @if($slot && $slot->volunteer)
                                @php $slotConflict = in_array($day->id, $verkeersregelaarDaysByVolunteer[$slot->volunteer_id] ?? []); @endphp
                                @if($slotConflict)<span class="text-danger" title="Staat ook als verkeersregelaar op deze dag">⛔</span> @endif{{ $slot->volunteer->name }}
                            @else
                            –
                            @endif
                            @endcan
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($volunteers->isEmpty())
<p class="text-muted mt-2">Voeg eerst vrijwilligers toe om ze in te plannen.</p>
@endif
@elseif($tab === 'verkeersregelaars')
<div class="card">
    <div class="card-body">
        <p class="text-muted mb-3">Verkeersregelaars staan langs de route. Plan ze per route in. Alleen bevoegde vrijwilligers die beschikbaar zijn op de dag(en) van de route worden getoond. Vrijwilligers die al in het rooster staan op die dag kunnen niet gekozen worden. <span class="text-warning">⚠️</span> = dubbel gepland op dezelfde dag. <span class="text-danger">⛔</span> = staat ook in het rooster op deze dag (niet mogelijk).</p>
        @forelse($walkRoutes as $route)
        @php
            $routeDayIds = $eventDaysByRoute[$route->id] ?? [];
            $routeDayNames = $eventDays->whereIn('id', $routeDayIds)->sortBy('sort_order')->pluck('name')->implode(', ') ?: 'alle dagen';
            $availableIds = $availableVerkeersregelaarsByRoute[$route->id] ?? [];
            $assignedIds = $route->volunteerRouteAssignments->pluck('volunteer_id')->toArray();
            $selectableVolunteers = $volunteers->whereIn('id', $availableIds)->whereNotIn('id', $assignedIds);
        @endphp
        <div class="border rounded p-3 mb-3">
            <strong>{{ $route->distance?->name ?? '-' }} – {{ $route->title ?: 'Route' }}</strong>
            <span class="text-muted small">({{ $routeDayNames }})</span>
            <ul class="mb-2 mt-2">
                @foreach($route->volunteerRouteAssignments as $ass)
                <li>
                    @if(($assignmentRosterConflict[$ass->volunteer_id][$route->id] ?? false))
                    <span class="text-danger" title="Staat ook in het rooster op deze dag – dit kan niet">⛔</span>
                    @elseif(($assignmentSameDayConflict[$ass->volunteer_id][$route->id] ?? false))
                    <span class="text-warning" title="Dubbel gepland op dezelfde dag – controleer of dit qua tijd haalbaar is">⚠️</span>
                    @endif
                    {{ $ass->volunteer?->name ?? '-' }}
                    @can('vrijwilligers_manage')
                    <form method="post" action="{{ route('intouch.volunteers.remove-verkeersregelaar') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="walk_route_id" value="{{ $route->id }}">
                        <input type="hidden" name="volunteer_id" value="{{ $ass->volunteer_id }}">
                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1">Verwijderen</button>
                    </form>
                    @endcan
                </li>
                @endforeach
            </ul>
            @can('vrijwilligers_manage')
            <form method="post" action="{{ route('intouch.volunteers.assign-verkeersregelaar') }}" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="hidden" name="walk_route_id" value="{{ $route->id }}">
                <select name="volunteer_id" class="form-select form-select-sm" style="max-width: 220px;">
                    <option value="">– kies vrijwilliger –</option>
                    @foreach($selectableVolunteers as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-vierdaagse">Toevoegen</button>
            </form>
            @if($selectableVolunteers->isEmpty() && !empty($assignedIds))
            <p class="text-muted small mb-0 mt-1">Alle beschikbare verkeersregelaars zijn al toegewezen.</p>
            @elseif($selectableVolunteers->isEmpty())
            <p class="text-muted small mb-0 mt-1">Geen bevoegde vrijwilligers beschikbaar op {{ $routeDayNames }}. <a href="{{ route('intouch.volunteers.create') }}">Voeg vrijwilligers toe</a> en vink "Bevoegd verkeersregelaar" aan.</p>
            @endif
            @endcan
        </div>
        @empty
        <p class="text-muted mb-0">Geen routes. Voeg routes toe via Werkgroep → Routes.</p>
        @endforelse
    </div>
</div>
@endif
@endsection
