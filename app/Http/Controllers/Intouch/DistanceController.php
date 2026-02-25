<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Distance;
use App\Models\EventDay;
use Illuminate\Http\Request;

class DistanceController extends Controller
{
    public function index()
    {
        $distances = Distance::query()->orderBy('sort_order')->get();

        return view('intouch.afstanden.index', compact('distances'));
    }

    public function create()
    {
        $eventDays = EventDay::query()->orderBy('sort_order')->get();

        return view('intouch.afstanden.create', compact('eventDays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kilometers' => ['required', 'numeric', 'min:0', 'max:999'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'event_day_sort_orders' => ['nullable', 'array'],
            'event_day_sort_orders.*' => ['integer', 'in:1,2,3,4'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['event_day_sort_orders'] = $this->normalizeEventDaySortOrders($request->input('event_day_sort_orders'));

        Distance::create($validated);

        return redirect()->route('intouch.afstanden.index')
            ->with('status', 'Afstand opgeslagen.');
    }

    public function show(Distance $distance)
    {
        return redirect()->route('intouch.afstanden.edit', $distance);
    }

    public function edit(Distance $distance)
    {
        $eventDays = EventDay::query()->orderBy('sort_order')->get();

        return view('intouch.afstanden.edit', compact('distance', 'eventDays'));
    }

    public function update(Request $request, Distance $distance)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kilometers' => ['required', 'numeric', 'min:0', 'max:999'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'event_day_sort_orders' => ['nullable', 'array'],
            'event_day_sort_orders.*' => ['integer', 'in:1,2,3,4'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['event_day_sort_orders'] = $this->normalizeEventDaySortOrders($request->input('event_day_sort_orders'));

        $distance->update($validated);

        return redirect()->route('intouch.afstanden.index')
            ->with('status', 'Afstand bijgewerkt.');
    }

    public function destroy(Distance $distance)
    {
        if ($distance->registrations()->exists()) {
            return redirect()->route('intouch.afstanden.index')
                ->with('error', 'Deze afstand heeft nog inschrijvingen en kan niet worden verwijderd.');
        }

        $distance->delete();

        return redirect()->route('intouch.afstanden.index')
            ->with('status', 'Afstand verwijderd.');
    }

    /** Leeg of alle vier avonden → null (loopt op alle avonden). */
    private function normalizeEventDaySortOrders(?array $value): ?array
    {
        if ($value === null || ! is_array($value)) {
            return null;
        }
        $ids = array_values(array_unique(array_map('intval', $value)));
        sort($ids);
        $all = [1, 2, 3, 4];
        if ($ids === $all || $ids === []) {
            return null;
        }

        return $ids;
    }
}
