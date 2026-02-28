<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\EventDay;
use App\Models\Volunteer;
use App\Models\VolunteerAvailability;
use App\Models\VolunteerRouteAssignment;
use App\Models\VolunteerSlot;
use App\Models\WalkRoute;
use Illuminate\Http\Request;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('vrijwilligers_view');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $tab = $request->get('tab', 'lijst');

        $volunteers = Volunteer::query()
            ->where('edition_id', $edition->id)
            ->with(['availabilities', 'availabilities.eventDay'])
            ->withCount('slots')
            ->orderBy('name')
            ->get();

        $availabilityByVolunteer = [];
        foreach ($volunteers as $v) {
            $availabilityByVolunteer[$v->id] = $v->availabilities->pluck('event_day_id')->toArray();
        }

        $eventDays = EventDay::query()
            ->where('edition_id', $edition->id)
            ->orderBy('sort_order')
            ->get();

        $roles = config('volunteers.roles', []);

        $slotsByDayRole = [];
        foreach ($eventDays as $day) {
            $slotsByDayRole[$day->id] = [];
            foreach (array_keys($roles) as $role) {
                $slot = VolunteerSlot::query()
                    ->where('event_day_id', $day->id)
                    ->where('role', $role)
                    ->with('volunteer')
                    ->first();
                $slotsByDayRole[$day->id][$role] = $slot;
            }
        }

        $walkRoutes = WalkRoute::query()
            ->where('edition_id', $edition->id)
            ->with(['distance', 'volunteerRouteAssignments.volunteer'])
            ->orderBy('sort_order')
            ->get();

        // Per route: event days waarop de route loopt
        $eventDaysByRoute = [];
        foreach ($walkRoutes as $route) {
            $orders = $route->event_day_sort_orders;
            if (empty($orders)) {
                $eventDaysByRoute[$route->id] = $eventDays->pluck('id')->toArray();
            } else {
                $eventDaysByRoute[$route->id] = $eventDays
                    ->whereIn('sort_order', array_map('intval', $orders))
                    ->pluck('id')
                    ->toArray();
            }
        }

        // Per route: beschikbare verkeersregelaars (bevoegd + beschikbaar op route-dagen)
        $availableVerkeersregelaarsByRoute = [];
        foreach ($walkRoutes as $route) {
            $routeDayIds = $eventDaysByRoute[$route->id] ?? [];
            $availableVerkeersregelaarsByRoute[$route->id] = $volunteers
                ->filter(fn ($v) => $v->can_regulate_traffic && ! empty(array_intersect($routeDayIds, $v->availabilities->pluck('event_day_id')->toArray())))
                ->pluck('id')
                ->toArray();
        }

        // Vrijwilligers die op meerdere routes staan (dubbel gepland)
        $volunteerRouteCounts = VolunteerRouteAssignment::query()
            ->whereIn('walk_route_id', $walkRoutes->pluck('id'))
            ->selectRaw('volunteer_id, COUNT(*) as cnt')
            ->groupBy('volunteer_id')
            ->pluck('cnt', 'volunteer_id');

        return view('intouch.volunteers.index', [
            'edition' => $edition,
            'volunteers' => $volunteers,
            'eventDays' => $eventDays,
            'walkRoutes' => $walkRoutes,
            'roles' => $roles,
            'slotsByDayRole' => $slotsByDayRole,
            'availabilityByVolunteer' => $availabilityByVolunteer,
            'eventDaysByRoute' => $eventDaysByRoute,
            'availableVerkeersregelaarsByRoute' => $availableVerkeersregelaarsByRoute,
            'volunteerRouteCounts' => $volunteerRouteCounts,
            'tab' => $tab,
        ]);
    }

    public function create()
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $eventDays = EventDay::query()->where('edition_id', $edition->id)->orderBy('sort_order')->get();

        return view('intouch.volunteers.form', [
            'volunteer' => null,
            'edition' => $edition,
            'eventDays' => $eventDays,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Geen editie geselecteerd.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'can_regulate_traffic' => ['nullable', 'boolean'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['integer', 'exists:event_days,id'],
        ]);

        $data['edition_id'] = $edition->id;
        $data['can_regulate_traffic'] = (bool) ($data['can_regulate_traffic'] ?? false);
        $availableDays = $data['available_days'] ?? [];
        unset($data['available_days']);

        $volunteer = Volunteer::create($data);

        foreach ($availableDays as $eventDayId) {
            $day = EventDay::find($eventDayId);
            if ($day && $day->edition_id === $edition->id) {
                VolunteerAvailability::create([
                    'volunteer_id' => $volunteer->id,
                    'event_day_id' => $eventDayId,
                ]);
            }
        }

        return redirect()->route('intouch.volunteers.index')
            ->with('status', 'Vrijwilliger toegevoegd.');
    }

    public function edit(Volunteer $volunteer)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition || $volunteer->edition_id !== $edition->id) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Vrijwilliger niet gevonden.');
        }

        $volunteer->load('availabilities');
        $eventDays = EventDay::query()->where('edition_id', $edition->id)->orderBy('sort_order')->get();

        return view('intouch.volunteers.form', [
            'volunteer' => $volunteer,
            'edition' => $edition,
            'eventDays' => $eventDays,
        ]);
    }

    public function update(Request $request, Volunteer $volunteer)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition || $volunteer->edition_id !== $edition->id) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Vrijwilliger niet gevonden.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'can_regulate_traffic' => ['nullable', 'boolean'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['integer', 'exists:event_days,id'],
        ]);

        $data['can_regulate_traffic'] = (bool) ($data['can_regulate_traffic'] ?? false);
        $availableDays = $data['available_days'] ?? [];
        unset($data['available_days']);

        $volunteer->update($data);

        $volunteer->availabilities()->delete();
        foreach ($availableDays as $eventDayId) {
            $day = EventDay::find($eventDayId);
            if ($day && $day->edition_id === $edition->id) {
                VolunteerAvailability::create([
                    'volunteer_id' => $volunteer->id,
                    'event_day_id' => $eventDayId,
                ]);
            }
        }

        return redirect()->route('intouch.volunteers.index')
            ->with('status', 'Vrijwilliger bijgewerkt.');
    }

    public function destroy(Volunteer $volunteer)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition || $volunteer->edition_id !== $edition->id) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Vrijwilliger niet gevonden.');
        }

        $volunteer->delete();

        return redirect()->route('intouch.volunteers.index')
            ->with('status', 'Vrijwilliger verwijderd.');
    }

    public function assignSlot(Request $request)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Geen editie geselecteerd.');
        }

        $data = $request->validate([
            'event_day_id' => ['required', 'exists:event_days,id'],
            'role' => ['required', 'string', 'in:' . implode(',', array_keys(config('volunteers.roles', [])))],
            'volunteer_id' => ['nullable', 'exists:volunteers,id'],
        ]);

        $eventDay = EventDay::findOrFail($data['event_day_id']);
        if ($eventDay->edition_id !== $edition->id) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Ongeldige dag.');
        }

        VolunteerSlot::query()
            ->where('event_day_id', $data['event_day_id'])
            ->where('role', $data['role'])
            ->delete();

        if (! empty($data['volunteer_id'])) {
            $volunteer = Volunteer::findOrFail($data['volunteer_id']);
            if ($volunteer->edition_id !== $edition->id) {
                return redirect()->route('intouch.volunteers.index')->with('error', 'Ongeldige vrijwilliger.');
            }
            VolunteerSlot::create([
                'volunteer_id' => $data['volunteer_id'],
                'event_day_id' => $data['event_day_id'],
                'role' => $data['role'],
            ]);
        }

        return redirect()->route('intouch.volunteers.index', ['tab' => 'rooster'])
            ->with('status', 'Rooster bijgewerkt.');
    }

    public function assignVerkeersregelaar(Request $request)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Geen editie geselecteerd.');
        }

        $data = $request->validate([
            'walk_route_id' => ['required', 'exists:walk_routes,id'],
            'volunteer_id' => ['nullable', 'exists:volunteers,id'],
        ]);

        $walkRoute = WalkRoute::findOrFail($data['walk_route_id']);
        if ($walkRoute->edition_id !== $edition->id) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Route niet gevonden.');
        }

        if (empty($data['volunteer_id'])) {
            return redirect()->route('intouch.volunteers.index', ['tab' => 'verkeersregelaars'])
                ->with('error', 'Selecteer een vrijwilliger.');
        }

        $volunteer = Volunteer::findOrFail($data['volunteer_id']);
        if ($volunteer->edition_id !== $edition->id) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Vrijwilliger niet gevonden.');
        }
        if (! $volunteer->can_regulate_traffic) {
            return redirect()->route('intouch.volunteers.index', ['tab' => 'verkeersregelaars'])
                ->with('error', 'Deze vrijwilliger is niet bevoegd om verkeer te regelen.');
        }
        $routeDayIds = $walkRoute->event_day_sort_orders
            ? EventDay::query()
                ->where('edition_id', $edition->id)
                ->whereIn('sort_order', array_map('intval', $walkRoute->event_day_sort_orders))
                ->pluck('id')
                ->toArray()
            : EventDay::query()->where('edition_id', $edition->id)->pluck('id')->toArray();
        $volunteerDayIds = $volunteer->availabilities()->pluck('event_day_id')->toArray();
        if (empty(array_intersect($routeDayIds, $volunteerDayIds))) {
            return redirect()->route('intouch.volunteers.index', ['tab' => 'verkeersregelaars'])
                ->with('error', 'Deze vrijwilliger is niet beschikbaar op de dag(en) waarop deze route loopt.');
        }

        VolunteerRouteAssignment::firstOrCreate([
            'volunteer_id' => $data['volunteer_id'],
            'walk_route_id' => $data['walk_route_id'],
        ]);

        return redirect()->route('intouch.volunteers.index', ['tab' => 'verkeersregelaars'])
            ->with('status', 'Verkeersregelaar toegevoegd aan route.');
    }

    public function removeVerkeersregelaar(Request $request)
    {
        $this->authorize('vrijwilligers_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.volunteers.index')->with('error', 'Geen editie geselecteerd.');
        }

        $data = $request->validate([
            'walk_route_id' => ['required', 'exists:walk_routes,id'],
            'volunteer_id' => ['required', 'exists:volunteers,id'],
        ]);

        $assignment = VolunteerRouteAssignment::query()
            ->where('walk_route_id', $data['walk_route_id'])
            ->where('volunteer_id', $data['volunteer_id'])
            ->first();

        if ($assignment) {
            $assignment->delete();
        }

        return redirect()->route('intouch.volunteers.index', ['tab' => 'verkeersregelaars'])
            ->with('status', 'Verkeersregelaar verwijderd van route.');
    }
}
