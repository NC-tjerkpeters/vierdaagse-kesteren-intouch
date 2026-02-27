<?php

namespace App\Http\Controllers\Routes;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\EventDay;
use App\Models\WalkRoute;
use Illuminate\Http\Request;

class PublicRoutesController extends Controller
{
    public function index(Request $request)
    {
        $edition = Edition::active();
        if (! $edition) {
            return view('routes.index', ['edition' => null, 'walkRoutes' => collect(), 'eventDays' => collect(), 'selectedDay' => null]);
        }

        $eventDays = EventDay::query()->where('edition_id', $edition->id)->orderBy('sort_order')->get();

        $selectedDay = null;
        $daySortOrder = $request->query('dag');
        if ($daySortOrder !== null && $daySortOrder !== '') {
            $selectedDay = $eventDays->firstWhere('sort_order', (int) $daySortOrder);
        }

        $walkRoutes = WalkRoute::query()
            ->where('edition_id', $edition->id)
            ->with(['distance', 'points'])
            ->orderBy('sort_order')
            ->get();

        if ($selectedDay) {
            $walkRoutes = $walkRoutes->filter(fn ($r) => $r->runsOnEventDay($selectedDay));
        }

        return view('routes.index', [
            'edition' => $edition,
            'walkRoutes' => $walkRoutes,
            'eventDays' => $eventDays,
            'selectedDay' => $selectedDay,
        ]);
    }

    public function show(WalkRoute $walkRoute)
    {
        $activeEdition = Edition::active();
        if (! $activeEdition || $walkRoute->edition_id !== $activeEdition->id) {
            abort(404);
        }

        $walkRoute->load(['distance', 'points']);

        return view('routes.show', ['walkRoute' => $walkRoute]);
    }

    public function pdf(WalkRoute $walkRoute)
    {
        $activeEdition = Edition::active();
        if (! $activeEdition || $walkRoute->edition_id !== $activeEdition->id) {
            abort(404);
        }
        if (! $walkRoute->pdf_path || ! \Storage::disk('public')->exists($walkRoute->pdf_path)) {
            abort(404);
        }

        return \Storage::disk('public')->response(
            $walkRoute->pdf_path,
            basename($walkRoute->pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
