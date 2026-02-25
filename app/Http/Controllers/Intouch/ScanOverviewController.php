<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Registration;
use Illuminate\Http\Request;

class ScanOverviewController extends Controller
{
    public function index()
    {
        $eventDays = EventDay::query()->orderBy('sort_order')->get();
        $currentDay = EventDay::getCurrent();

        $registrationIds = Registration::query()
            ->where('mollie_payment_status', 'paid')
            ->whereNotNull('qr_code')
            ->pluck('id');

        $registrationsByDistance = Registration::query()
            ->with('distance')
            ->whereIn('id', $registrationIds)
            ->get()
            ->groupBy('distance_id');

        $distances = \App\Models\Distance::query()
            ->orderBy('sort_order')
            ->get();

        $overview = [];
        foreach ($eventDays as $day) {
            $points = $day->startPostFinishPointNumbers();
            $overview[$day->id] = [
                'day' => $day,
                'points' => $points,
                'by_distance' => [],
            ];

            foreach ($distances as $distance) {
                if (! $distance->runsOnEventDay($day)) {
                    continue;
                }
                $regIds = $registrationsByDistance->get($distance->id)?->pluck('id') ?? collect();
                $total = $regIds->count();

                $startCount = $regIds->isEmpty() ? 0 : Registration::query()
                    ->whereIn('id', $regIds)
                    ->where('usage_count', '>=', $points['start'])
                    ->count();
                $postCount = $regIds->isEmpty() ? 0 : Registration::query()
                    ->whereIn('id', $regIds)
                    ->where('usage_count', '>=', $points['post'])
                    ->count();
                $finishCount = $regIds->isEmpty() ? 0 : Registration::query()
                    ->whereIn('id', $regIds)
                    ->where('usage_count', '>=', $points['finish'])
                    ->count();

                $missing = $regIds->isEmpty() ? collect() : Registration::query()
                    ->whereIn('id', $regIds)
                    ->where('usage_count', '<', $points['finish'])
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get(['id', 'first_name', 'last_name', 'phone_number']);

                $currentSort = $currentDay ? (int) $currentDay->sort_order : null;
                $daySort = (int) $day->sort_order;
                if ($currentSort !== null && $daySort > $currentSort) {
                    $startCount = 0;
                    $postCount = 0;
                    $finishCount = 0;
                    $missing = collect();
                }

                $overview[$day->id]['by_distance'][$distance->id] = [
                    'distance' => $distance,
                    'total' => $total,
                    'start' => $startCount,
                    'post' => $postCount,
                    'finish' => $finishCount,
                    'missing' => $missing,
                ];
            }
        }

        $totalParticipants = $registrationIds->count();

        return view('intouch.scan-overview.index', [
            'eventDays' => $eventDays,
            'currentDay' => $currentDay,
            'distances' => $distances,
            'overview' => $overview,
            'totalParticipants' => $totalParticipants,
        ]);
    }

    public function setCurrentDay(Request $request)
    {
        $request->validate([
            'event_day_id' => ['required', 'exists:event_days,id'],
        ]);

        EventDay::query()->update(['is_current' => false]);
        $newCurrentDay = EventDay::query()->findOrFail($request->event_day_id);
        $newCurrentDay->update(['is_current' => true]);

        $activationPoint = match ((int) $newCurrentDay->sort_order) {
            1 => 1,
            2 => 4,
            3 => 7,
            4 => 10,
            default => null,
        };

        if ($activationPoint !== null) {
            Registration::query()
                ->where('mollie_payment_status', 'paid')
                ->whereNotNull('qr_code')
                ->update(['usage_count' => $activationPoint]);
        }

        return redirect()->route('intouch.scan-overview.index')
            ->with('status', 'Huidige avond is bijgewerkt. Iedereen start weer op hetzelfde punt voor deze avond.');
    }
}
