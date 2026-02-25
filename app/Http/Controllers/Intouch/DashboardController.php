<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Distance;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRegistrations = Registration::query()->count();
        $paidCount = Registration::query()->where('mollie_payment_status', 'paid')->count();
        $withMedal = Registration::query()->where('wants_medal', true)->count();

        $byDistance = Registration::query()
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
