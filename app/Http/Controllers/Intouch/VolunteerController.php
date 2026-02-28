<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\EventDay;
use App\Models\Volunteer;
use App\Models\VolunteerSlot;
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
            ->withCount('slots')
            ->orderBy('name')
            ->get();

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

        return view('intouch.volunteers.index', [
            'edition' => $edition,
            'volunteers' => $volunteers,
            'eventDays' => $eventDays,
            'roles' => $roles,
            'slotsByDayRole' => $slotsByDayRole,
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

        return view('intouch.volunteers.form', [
            'volunteer' => null,
            'edition' => $edition,
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
        ]);

        $data['edition_id'] = $edition->id;
        Volunteer::create($data);

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

        return view('intouch.volunteers.form', [
            'volunteer' => $volunteer,
            'edition' => $edition,
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
        ]);

        $volunteer->update($data);

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
}
