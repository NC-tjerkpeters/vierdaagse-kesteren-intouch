<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Distance;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('dashboard_view');

        $totalRegistrations = Registration::query()->forActiveEdition()->count();
        $paidCount = Registration::query()->forActiveEdition()->where('mollie_payment_status', 'paid')->count();
        $withMedal = Registration::query()->forActiveEdition()->where('wants_medal', true)->count();

        $byDistance = Registration::query()
            ->forActiveEdition()
            ->selectRaw('distance_id, count(*) as total')
            ->where('mollie_payment_status', 'paid')
            ->groupBy('distance_id')
            ->with('distance:id,name')
            ->get()
            ->keyBy('distance_id');

        return view('intouch.dashboard', [
            'totalRegistrations' => $totalRegistrations,
            'paidCount' => $paidCount,
            'withMedal' => $withMedal,
            'byDistance' => $byDistance,
            'distances' => Distance::query()->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}
