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

        return view('scanner.index', [
            'currentDay' => $currentDay,
        ]);
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

        $registration = $this->resolveRegistrationFromQr($request->qr_data);
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

        $minMinutes = (int) config('scanner.min_minutes_between_scans', 5);
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

        $pointName = config('scanner.point_names.' . $pointNumber, 'Punt ' . $pointNumber);
        $medalInfo = $registration->wants_medal && $registration->medal_number
            ? ' (medaille ' . $registration->medal_number . ')'
            : '';

        return redirect()->route('scanner.index')
            ->with('success', $registration->first_name . ' ' . $registration->last_name . $medalInfo . ' – ' . $pointName . ' geregistreerd.');
    }

    /** API voor mobiele scanner: JSON in, JSON uit (zoals jullie store.php). */
    public function storeApi(Request $request): JsonResponse
    {
        $qrData = $request->input('qrcode_values') ?? $request->input('qr_data') ?? '';

        $currentDay = EventDay::getCurrent();
        if (! $currentDay) {
            return response()->json(['status' => 'error', 'message' => 'Er is geen actieve avond gekozen. Stel in Intouch de huidige avond in.']);
        }

        $registration = $this->resolveRegistrationFromQr((string) $qrData);
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

        $minMinutes = (int) config('scanner.min_minutes_between_scans', 5);
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

        $pointName = config('scanner.point_names.' . $pointNumber, 'Punt ' . $pointNumber);
        $medalInfo = $registration->wants_medal && $registration->medal_number
            ? ' (medaille ' . $registration->medal_number . ')'
            : '';
        $message = $registration->first_name . ' ' . $registration->last_name . $medalInfo . ' – ' . $pointName . ' geregistreerd.';

        return response()->json(['status' => 'ok', 'message' => $message]);
    }

    protected function resolveRegistrationFromQr(string $qrData): ?Registration
    {
        $qrData = trim($qrData);
        if (str_starts_with($qrData, 'vierdaagsekesteren:')) {
            $parts = explode(':', $qrData, 3);
            $id = (int) ($parts[1] ?? 0);
            if ($id > 0) {
                $reg = Registration::query()
                    ->where('id', $id)
                    ->where('mollie_payment_status', 'paid')
                    ->whereNotNull('qr_code')
                    ->first();
                if ($reg && $reg->qr_code === $qrData) {
                    return $reg;
                }
                return Registration::query()
                    ->where('id', $id)
                    ->where('mollie_payment_status', 'paid')
                    ->whereNotNull('qr_code')
                    ->first();
            }
        }
        if (ctype_digit($qrData)) {
            return Registration::query()
                ->where('id', (int) $qrData)
                ->where('mollie_payment_status', 'paid')
                ->whereNotNull('qr_code')
                ->first();
        }
        return null;
    }
}
