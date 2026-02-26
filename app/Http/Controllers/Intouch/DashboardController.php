<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\CostEntry;
use App\Models\Edition;
use App\Models\Registration;
use App\Models\Sponsor;
use App\Models\Distance;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('dashboard_view');

        $edition = Edition::current();
        if (! $edition) {
            return view('intouch.dashboard', [
                'edition' => null,
                'totalRegistrations' => 0,
                'paidCount' => 0,
                'withMedal' => 0,
                'byDistance' => collect(),
                'distances' => Distance::query()->orderBy('sort_order')->get(['id', 'name']),
                'revenueDeelnemers' => 0,
                'revenueSponsors' => 0,
                'totalCosts' => 0,
                'result' => 0,
                'closingBalance' => 0,
                'sponsorTotaal' => 0,
                'sponsorDoel' => config('sponsors.doelbedrag', 1850),
                'sponsorProgress' => 0,
            ]);
        }

        $totalRegistrations = Registration::query()->where('edition_id', $edition->id)->count();
        $paidCount = Registration::query()->where('edition_id', $edition->id)->where('mollie_payment_status', 'paid')->count();
        $withMedal = Registration::query()->where('edition_id', $edition->id)->where('wants_medal', true)->count();

        $byDistance = Registration::query()
            ->where('edition_id', $edition->id)
            ->selectRaw('distance_id, count(*) as total')
            ->where('mollie_payment_status', 'paid')
            ->groupBy('distance_id')
            ->with('distance:id,name')
            ->get()
            ->keyBy('distance_id');

        $revenueDeelnemers = Registration::query()
            ->where('edition_id', $edition->id)
            ->where('mollie_payment_status', 'paid')
            ->with('distance')
            ->get()
            ->sum(fn ($r) => (float) ($r->distance->price ?? 0));
        $revenueSponsors = Sponsor::query()
            ->where('edition_id', $edition->id)
            ->where('betaalstatus', 'paid')
            ->sum('bedrag');
        $totalCosts = CostEntry::query()->where('edition_id', $edition->id)->sum('amount');
        $result = ($revenueDeelnemers + $revenueSponsors) - $totalCosts;
        $closingBalance = (float) ($edition->opening_balance ?? 0) + $result;

        $sponsorDoel = config('sponsors.doelbedrag', 1850);
        $sponsorTotaal = Sponsor::query()
            ->where('edition_id', $edition->id)
            ->where('betaalstatus', 'paid')
            ->sum('bedrag');
        $sponsorProgress = $sponsorDoel > 0 ? min(100, ($sponsorTotaal / $sponsorDoel) * 100) : 0;

        return view('intouch.dashboard', [
            'edition' => $edition,
            'totalRegistrations' => $totalRegistrations,
            'paidCount' => $paidCount,
            'withMedal' => $withMedal,
            'byDistance' => $byDistance,
            'distances' => Distance::query()->orderBy('sort_order')->get(['id', 'name']),
            'revenueDeelnemers' => $revenueDeelnemers,
            'revenueSponsors' => $revenueSponsors,
'totalCosts' => $totalCosts,
                'result' => $result,
                'closingBalance' => $closingBalance,
            'sponsorTotaal' => $sponsorTotaal,
            'sponsorDoel' => $sponsorDoel,
            'sponsorProgress' => $sponsorProgress,
        ]);
    }
}
