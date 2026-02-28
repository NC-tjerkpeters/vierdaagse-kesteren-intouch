<?php

namespace App\Http\Controllers\Scanner;

use App\Http\Controllers\Controller;
use App\Models\EventDay;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        $currentDay = EventDay::getCurrent();
        $overview = $this->buildCompactOverview($currentDay);

        return view('scanner.index', [
            'currentDay' => $currentDay,
            'overview' => $overview,
        ]);
    }

    protected function buildCompactOverview(?EventDay $currentDay): array
    {
        if (! $currentDay) {
            return ['rows' => [], 'totals' => ['start' => 0, 'post' => 0, 'finish' => 0]];
        }

        $registrationIds = Registration::query()
            ->where('edition_id', $currentDay->edition_id)
            ->where('mollie_payment_status', 'paid')
            ->whereNotNull('qr_code')
            ->pluck('id');

        $registrationsByDistance = Registration::query()
            ->where('edition_id', $currentDay->edition_id)
            ->with('distance')
            ->whereIn('id', $registrationIds)
            ->get()
            ->groupBy('distance_id');

        $distances = \App\Models\Distance::query()->orderBy('sort_order')->get();
        $points = $currentDay->startPostFinishPointNumbers();
        $rows = [];
        $totals = ['start' => 0, 'post' => 0, 'finish' => 0];

        foreach ($distances as $distance) {
            if (! $distance->runsOnEventDay($currentDay)) {
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

            $totals['start'] += $startCount;
            $totals['post'] += $postCount;
            $totals['finish'] += $finishCount;

            $rows[] = [
                'distance' => $distance->name,
                'start' => $startCount,
                'post' => $postCount,
                'finish' => $finishCount,
            ];
        }

        return ['rows' => $rows, 'totals' => $totals];
    }

    public function overviewApi(): JsonResponse
    {
        $currentDay = EventDay::getCurrent();
        $overview = $this->buildCompactOverview($currentDay);

        return response()->json($overview);
    }

    public function store(Request $request)
    {
        $request->validate([
            'qr_data' => ['required', 'string', 'max:500'],
        ]);

        $currentDay = EventDay::getCurrent();
        if (! $currentDay) {
            return redirect()->route('scanner.index')
                ->with('error', 'Er is geen actieve avond gekozen. Stel in Intouch de huidige avond in.');
        }

        $registration = $this->resolveRegistrationFromQr($request->qr_data, $currentDay->edition_id);
        if (! $registration) {
            return redirect()->route('scanner.index')
                ->with('error', 'Ongeldige of onbekende QR-code.');
        }

        if (! $registration->distance->runsOnEventDay($currentDay)) {
            return redirect()->route('scanner.index')
                ->with('error', $registration->distance->name . ' loopt vandaag niet. Deze afstand loopt alleen op bepaalde avonden.');
        }

        $allowedNumbers = $currentDay->allowedPointNumbers();
        $usage = (int) $registration->usage_count;
        $pointNumber = collect($allowedNumbers)->first(fn (int $n) => $n > $usage);

        if ($pointNumber === null || ! in_array($pointNumber, $allowedNumbers, true)) {
            return redirect()->route('scanner.index')
                ->with('info', $registration->first_name . ' ' . $registration->last_name . ' heeft ' . $currentDay->name . ' al voltooid (alle punten gescand).');
        }

        $minMinutes = \App\Services\AppSettings::scannerMinMinutes();
        if ($minMinutes > 0 && $registration->last_scan_at) {
            $minNextAt = $registration->last_scan_at->addMinutes($minMinutes);
            if (now()->lt($minNextAt)) {
                $wait = (int) ceil(now()->diffInSeconds($minNextAt, false) / 60);
                return redirect()->route('scanner.index')
                    ->with('error', 'Wacht nog ' . $wait . ' minuten tot de volgende scan. (Minimaal ' . $minMinutes . ' min tussen twee scans.)');
            }
        }

        $registration->update([
            'usage_count' => max($usage, $pointNumber),
            'last_scan_at' => now(),
        ]);

        $pointNames = \App\Services\AppSettings::scannerPointNames();
        $pointName = $pointNames[$pointNumber] ?? ('Punt ' . $pointNumber);
        $medalInfo = $registration->wants_medal && $registration->medal_number
            ? ' (medaille ' . $registration->medal_number . ')'
            : '';
        $distanceName = $registration->distance->name ?? '';

        return redirect()->route('scanner.index')
            ->with('success', $registration->first_name . ' ' . $registration->last_name . ' – ' . $distanceName . $medalInfo . ' – ' . $pointName . ' geregistreerd.');
    }

    /** API voor mobiele scanner: JSON in, JSON uit (zoals jullie store.php). */
    public function storeApi(Request $request): JsonResponse
    {
        $qrData = $request->input('qrcode_values') ?? $request->input('qr_data') ?? '';

        $currentDay = EventDay::getCurrent();
        if (! $currentDay) {
            return response()->json(['status' => 'error', 'message' => 'Er is geen actieve avond gekozen. Stel in Intouch de huidige avond in.']);
        }

        $registration = $this->resolveRegistrationFromQr((string) $qrData, $currentDay->edition_id);
        if (! $registration) {
            return response()->json(['status' => 'error', 'message' => 'Ongeldige of onbekende QR-code.']);
        }

        if (! $registration->distance->runsOnEventDay($currentDay)) {
            return response()->json([
                'status' => 'error',
                'message' => $registration->distance->name . ' loopt vandaag niet. Deze afstand loopt alleen op bepaalde avonden.',
            ]);
        }

        $allowedNumbers = $currentDay->allowedPointNumbers();
        $usage = (int) $registration->usage_count;
        $pointNumber = collect($allowedNumbers)->first(fn (int $n) => $n > $usage);

        if ($pointNumber === null || ! in_array($pointNumber, $allowedNumbers, true)) {
            return response()->json([
                'status' => 'info',
                'message' => $registration->first_name . ' ' . $registration->last_name . ' heeft ' . $currentDay->name . ' al voltooid (alle punten gescand).',
            ]);
        }

        $minMinutes = \App\Services\AppSettings::scannerMinMinutes();
        if ($minMinutes > 0 && $registration->last_scan_at) {
            $minNextAt = $registration->last_scan_at->addMinutes($minMinutes);
            if (now()->lt($minNextAt)) {
                $wait = (int) ceil(now()->diffInSeconds($minNextAt, false) / 60);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wacht nog ' . $wait . ' minuten tot de volgende scan. (Min. ' . $minMinutes . ' min tussen twee scans.)',
                ]);
            }
        }

        $registration->update([
            'usage_count' => max($usage, $pointNumber),
            'last_scan_at' => now(),
        ]);

        $pointNames = \App\Services\AppSettings::scannerPointNames();
        $pointName = $pointNames[$pointNumber] ?? ('Punt ' . $pointNumber);
        $medalInfo = $registration->wants_medal && $registration->medal_number
            ? ' (medaille ' . $registration->medal_number . ')'
            : '';
        $distanceName = $registration->distance->name ?? '';
        $message = $registration->first_name . ' ' . $registration->last_name . ' – ' . $distanceName . $medalInfo . ' – ' . $pointName . ' geregistreerd.';

        return response()->json(['status' => 'ok', 'message' => $message]);
    }

    protected function resolveRegistrationFromQr(string $qrData, int $editionId): ?Registration
    {
        $qrData = trim($qrData);
        if (str_starts_with($qrData, 'vierdaagsekesteren:')) {
            $parts = explode(':', $qrData, 3);
            $id = (int) ($parts[1] ?? 0);
            if ($id > 0) {
                $reg = Registration::query()
                    ->where('id', $id)
                    ->where('edition_id', $editionId)
                    ->where('mollie_payment_status', 'paid')
                    ->whereNotNull('qr_code')
                    ->first();
                if ($reg && $reg->qr_code === $qrData) {
                    return $reg;
                }
                return null;
            }
        }
        if (config('app.scanner_allow_numeric_id_fallback', true) && ctype_digit($qrData)) {
            return Registration::query()
                ->where('id', (int) $qrData)
                ->where('edition_id', $editionId)
                ->where('mollie_payment_status', 'paid')
                ->whereNotNull('qr_code')
                ->first();
        }
        return null;
    }
}
