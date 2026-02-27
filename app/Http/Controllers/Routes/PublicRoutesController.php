<?php

namespace App\Http\Controllers\Routes;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\WalkRoute;

class PublicRoutesController extends Controller
{
    public function index()
    {
        $edition = Edition::active();
        if (! $edition) {
            return view('routes.index', ['edition' => null, 'walkRoutes' => collect()]);
        }

        $walkRoutes = WalkRoute::query()
            ->where('edition_id', $edition->id)
            ->with(['distance', 'points'])
            ->orderBy('sort_order')
            ->get();

        return view('routes.index', [
            'edition' => $edition,
            'walkRoutes' => $walkRoutes,
        ]);
    }

    public function show(WalkRoute $walkRoute)
    {
        $walkRoute->load(['distance', 'points']);

        return view('routes.show', ['walkRoute' => $walkRoute]);
    }
}
