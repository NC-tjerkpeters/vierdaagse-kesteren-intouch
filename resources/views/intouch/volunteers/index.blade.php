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
                    <td colspan="6" class="text-muted">Nog geen vrijwilligers. @can('vrijwilligers_manage')<a href="{{ route('intouch.volunteers.create') }}">Voeg er een toe</a>.@endcan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
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
                            <form method="post" action="{{ route('intouch.volunteers.assign-slot') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="event_day_id" value="{{ $day->id }}">
                                <input type="hidden" name="role" value="{{ $roleKey }}">
                                <select name="volunteer_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">– kies –</option>
                                    @foreach($volunteers as $v)
                                    @php $available = in_array($day->id, $availabilityByVolunteer[$v->id] ?? []); @endphp
                                    <option value="{{ $v->id }}" @selected($slot && $slot->volunteer_id === $v->id)>
                                        {{ $v->name }}{{ !$available ? ' (niet beschikbaar)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                            @else
                            {{ $slot && $slot->volunteer ? $slot->volunteer->name : '–' }}
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
@endif
@endsection
