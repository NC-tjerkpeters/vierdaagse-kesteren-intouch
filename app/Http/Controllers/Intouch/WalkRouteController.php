<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
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

        return view('intouch.routes.index', [
            'edition' => $edition,
            'walkRoutes' => $walkRoutes,
            'distances' => $distances,
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

        return view('intouch.routes.create', [
            'edition' => $edition,
            'distances' => $distances,
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
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $route = WalkRoute::create([
            'edition_id' => $edition->id,
            'distance_id' => $data['distance_id'],
            'title' => $data['title'],
            'description' => $data['description'],
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

        return view('intouch.routes.edit', ['walkRoute' => $walkRoute]);
    }

    public function update(Request $request, WalkRoute $walkRoute)
    {
        $this->authorize('routes_manage');

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $walkRoute->update([
            'title' => $data['title'],
            'description' => $data['description'],
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
}
