<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\EventDay;
use App\Models\WalkRoute;
use Illuminate\Http\Request;

class WalkRouteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('routes_view');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie (rechtsboven in het menu).');
        }

        $walkRoutes = WalkRoute::query()
            ->where('edition_id', $edition->id)
            ->with(['distance', 'points'])
            ->orderBy('sort_order')
            ->get();

        $distances = \App\Models\Distance::query()->orderBy('sort_order')->get();
        $eventDays = EventDay::query()->where('edition_id', $edition->id)->orderBy('sort_order')->get()->keyBy('sort_order');
        $routeTemplates = \App\Models\RouteTemplate::query()
            ->with(['distance', 'points'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('intouch.routes.index', [
            'edition' => $edition,
            'walkRoutes' => $walkRoutes,
            'distances' => $distances,
            'eventDays' => $eventDays,
            'routeTemplates' => $routeTemplates,
        ]);
    }

    public function create()
    {
        $this->authorize('routes_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie (rechtsboven in het menu).');
        }

        $distances = \App\Models\Distance::query()->orderBy('sort_order')->get();
        $eventDays = EventDay::query()->where('edition_id', $edition->id)->orderBy('sort_order')->get();

        return view('intouch.routes.create', [
            'edition' => $edition,
            'distances' => $distances,
            'eventDays' => $eventDays,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('routes_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.walk-routes.index')->with('error', 'Geen editie geselecteerd.');
        }

        $data = $request->validate([
            'distance_id' => ['required', 'exists:distances,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_day_sort_orders' => ['nullable', 'array'],
            'event_day_sort_orders.*' => ['integer', 'min:1'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $route = WalkRoute::create([
            'edition_id' => $edition->id,
            'distance_id' => $data['distance_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'event_day_sort_orders' => $this->normalizeEventDaySortOrders($request->input('event_day_sort_orders')),
            'sort_order' => WalkRoute::where('edition_id', $edition->id)->max('sort_order') + 1,
        ]);

        if ($request->hasFile('pdf')) {
            $route->pdf_path = $route->storePdf($request->file('pdf'));
            $route->save();
        }

        return redirect()->route('intouch.walk-routes.edit', $route)
            ->with('status', 'Route aangemaakt. Voeg eventueel punten toe.');
    }

    public function edit(WalkRoute $walkRoute)
    {
        $this->authorize('routes_manage');

        $walkRoute->load(['edition', 'distance', 'points']);
        $eventDays = EventDay::query()->where('edition_id', $walkRoute->edition_id)->orderBy('sort_order')->get();

        return view('intouch.routes.edit', [
            'walkRoute' => $walkRoute,
            'eventDays' => $eventDays,
        ]);
    }

    public function update(Request $request, WalkRoute $walkRoute)
    {
        $this->authorize('routes_manage');

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_day_sort_orders' => ['nullable', 'array'],
            'event_day_sort_orders.*' => ['integer', 'min:1'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $walkRoute->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'event_day_sort_orders' => $this->normalizeEventDaySortOrders($request->input('event_day_sort_orders')),
        ]);

        if ($request->hasFile('pdf')) {
            $walkRoute->deletePdf();
            $walkRoute->pdf_path = $walkRoute->storePdf($request->file('pdf'));
            $walkRoute->save();
        }

        $walkRoute->points()->delete();
        $points = $request->input('points', []);
        foreach ($points as $i => $p) {
            $name = is_array($p) ? ($p['name'] ?? '') : $p;
            if (trim((string) $name) !== '') {
                $walkRoute->points()->create(['name' => trim($name), 'sort_order' => $i]);
            }
        }

        return redirect()->route('intouch.walk-routes.index')->with('status', 'Route bijgewerkt.');
    }

    public function destroy(WalkRoute $walkRoute)
    {
        $this->authorize('routes_manage');

        $walkRoute->deletePdf();
        $walkRoute->delete();

        return redirect()->route('intouch.walk-routes.index')->with('status', 'Route verwijderd.');
    }

    public function deletePdf(WalkRoute $walkRoute)
    {
        $this->authorize('routes_manage');

        $walkRoute->deletePdf();

        return redirect()->route('intouch.walk-routes.edit', $walkRoute)->with('status', 'PDF verwijderd.');
    }

    /** Leeg of alle dagen → null (actief op alle dagen). */
    private function normalizeEventDaySortOrders(?array $value): ?array
    {
        if ($value === null || ! is_array($value)) {
            return null;
        }
        $ids = array_values(array_unique(array_map('intval', array_filter($value))));
        sort($ids);
        if ($ids === []) {
            return null;
        }

        return $ids;
    }

    public function addFromLibraryForm()
    {
        $this->authorize('routes_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $templates = \App\Models\RouteTemplate::query()
            ->with(['distance', 'points'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
        $eventDays = EventDay::query()->where('edition_id', $edition->id)->orderBy('sort_order')->get();

        return view('intouch.routes.add-from-library', [
            'edition' => $edition,
            'templates' => $templates,
            'eventDays' => $eventDays,
        ]);
    }

    public function addFromLibrary(Request $request)
    {
        $this->authorize('routes_manage');

        $edition = Edition::current();
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('info', 'Selecteer eerst een editie.');
        }

        $data = $request->validate([
            'route_template_id' => ['required', 'exists:route_templates,id'],
            'event_day_sort_orders' => ['nullable', 'array'],
            'event_day_sort_orders.*' => ['integer', 'min:1'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $template = \App\Models\RouteTemplate::with(['distance', 'points'])->findOrFail($data['route_template_id']);
        $eventDaySortOrders = $this->normalizeEventDaySortOrders($data['event_day_sort_orders'] ?? null);

        $route = WalkRoute::create([
            'edition_id' => $edition->id,
            'route_template_id' => $template->id,
            'distance_id' => $template->distance_id,
            'title' => $template->title,
            'description' => $template->description,
            'event_day_sort_orders' => $eventDaySortOrders,
            'sort_order' => WalkRoute::where('edition_id', $edition->id)->max('sort_order') + 1,
        ]);

        foreach ($template->points as $i => $point) {
            $route->points()->create(['name' => $point->name, 'sort_order' => $i]);
        }

        if ($request->hasFile('pdf')) {
            $route->pdf_path = $route->storePdf($request->file('pdf'));
            $route->save();
        } elseif ($template->pdf_path && \Storage::disk('public')->exists($template->pdf_path)) {
            $contents = \Storage::disk('public')->get($template->pdf_path);
            $newPath = 'route-pdfs/' . $edition->id . '/' . basename($template->pdf_path);
            \Storage::disk('public')->put($newPath, $contents);
            $route->update(['pdf_path' => $newPath]);
        }

        return redirect()->route('intouch.walk-routes.edit', $route)
            ->with('status', 'Route toegevoegd vanuit bibliotheek. Controleer de dagen en PDF.');
    }
}
