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
        $this->authorize('loopoverzicht_view');

        $eventDays = EventDay::query()->forActiveEdition()->orderBy('sort_order')->get();
        $currentDay = EventDay::getCurrent();

        $registrationIds = Registration::query()
            ->where('mollie_payment_status', 'paid')
            ->whereNotNull('qr_code')
            ->pluck('id');

        $registrationsByDistance = Registration::query()
            ->forActiveEdition()
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

        $currentEdition = \App\Models\Edition::current();
        $activeEdition = \App\Models\Edition::active();
        $isViewingArchive = $activeEdition && $currentEdition && $currentEdition->id !== $activeEdition->id;
        $canSetCurrentDay = !$isViewingArchive;
        $viewCurrentDay = $isViewingArchive ? null : $currentDay;
        $archiveCurrentDayName = $isViewingArchive ? $eventDays->where('is_current', true)->first()?->name : null;

        return view('intouch.scan-overview.index', [
            'eventDays' => $eventDays,
            'currentDay' => $viewCurrentDay,
            'archiveCurrentDayName' => $archiveCurrentDayName,
            'distances' => $distances,
            'overview' => $overview,
            'totalParticipants' => $totalParticipants,
            'canSetCurrentDay' => $canSetCurrentDay,
        ]);
    }

    public function setCurrentDay(Request $request)
    {
        $this->authorize('loopoverzicht_view');

        $request->validate([
            'event_day_id' => ['required', 'exists:event_days,id'],
        ]);

        $activeEdition = \App\Models\Edition::active();
        if (!$activeEdition) {
            return redirect()->route('intouch.scan-overview.index')->with('error', 'Geen actieve editie.');
        }
        $newCurrentDay = EventDay::query()->where('edition_id', $activeEdition->id)->findOrFail($request->event_day_id);
        EventDay::query()->where('edition_id', $activeEdition->id)->update(['is_current' => false]);
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
                ->forActiveEdition()
                ->where('mollie_payment_status', 'paid')
                ->whereNotNull('qr_code')
                ->update(['usage_count' => $activationPoint]);
        }

        return redirect()->route('intouch.scan-overview.index')
            ->with('status', 'Huidige avond is bijgewerkt. Iedereen start weer op hetzelfde punt voor deze avond.');
    }
}
