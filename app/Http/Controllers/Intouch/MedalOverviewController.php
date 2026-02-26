<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class MedalOverviewController extends Controller
{
    public function index()
    {
        $this->authorize('inschrijvingen_medal_overview');

        $medals = Registration::query()
            ->where('mollie_payment_status', 'paid')
            ->where('wants_medal', true)
            ->select('medal_number', DB::raw('COUNT(*) as aantal'))
            ->groupBy('medal_number')
            ->orderByRaw('COALESCE(medal_number, 0)')
            ->get()
            ->map(function ($row) {
                $num = $row->medal_number;
                $label = $num
                    ? (match ($num) {
                        1 => '1e keer',
                        2 => '2e keer',
                        3 => '3e keer',
                        4 => '4e keer',
                        5 => '5e keer',
                        default => "{$num}e keer",
                    })
                    : 'Geen nummer';
                return [
                    'medal_number' => $num,
                    'label' => $label,
                    'aantal' => (int) $row->aantal,
                ];
            });

        $totaalMedailles = $medals->sum('aantal');
        $totaalMetMedaille = Registration::query()
            ->where('mollie_payment_status', 'paid')
            ->where('wants_medal', true)
            ->count();

        return view('intouch.medal-overview.index', [
            'medals' => $medals,
            'totaalMedailles' => $totaalMedailles,
            'totaalMetMedaille' => $totaalMetMedaille,
        ]);
    }
}
