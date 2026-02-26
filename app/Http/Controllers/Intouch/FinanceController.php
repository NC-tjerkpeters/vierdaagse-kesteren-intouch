<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\CostEntry;
use App\Models\Edition;
use App\Models\Registration;
use App\Models\Sponsor;
use App\Services\MollieFeeCalculator;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('finances_view');

        $edition = $this->resolveEdition($request);
        if (! $edition) {
            return redirect()->route('intouch.dashboard')
                ->with('error', 'Geen editie geselecteerd. Start eerst een editie.');
        }

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

        $totalRevenue = $revenueDeelnemers + $revenueSponsors;

        $costsByCategory = CostEntry::query()
            ->where('edition_id', $edition->id)
            ->get()
            ->groupBy('category');

        $totalCosts = CostEntry::query()
            ->where('edition_id', $edition->id)
            ->sum('amount');

        $result = $totalRevenue - $totalCosts;

        $editions = Edition::query()->orderByDesc('start_date')->get();

        return view('intouch.finance.index', [
            'edition' => $edition,
            'editions' => $editions,
            'revenueDeelnemers' => $revenueDeelnemers,
            'revenueSponsors' => $revenueSponsors,
            'totalRevenue' => $totalRevenue,
            'costsByCategory' => $costsByCategory,
            'totalCosts' => $totalCosts,
            'result' => $result,
        ]);
    }

    public function createCost(Request $request)
    {
        $this->authorize('finances_edit');

        $edition = $this->resolveEdition($request);
        if (! $edition) {
            return redirect()->route('intouch.finance.index')->with('error', 'Geen editie geselecteerd.');
        }

        $editions = Edition::query()->orderByDesc('start_date')->get();

        return view('intouch.finance.cost-form', [
            'cost' => null,
            'edition' => $edition,
            'editions' => $editions,
        ]);
    }

    public function storeCost(Request $request)
    {
        $this->authorize('finances_edit');

        $validated = $request->validate([
            'edition_id' => ['required', 'exists:editions,id'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'in:mollie,medailles,overig'],
            'cost_date' => ['required', 'date'],
        ]);

        CostEntry::create($validated);

        return redirect()->route('intouch.finance.index', ['edition_id' => $validated['edition_id']])
            ->with('status', 'Kost toegevoegd.');
    }

    public function editCost(CostEntry $cost)
    {
        $this->authorize('finances_edit');

        $editions = Edition::query()->orderByDesc('start_date')->get();

        return view('intouch.finance.cost-form', [
            'cost' => $cost,
            'edition' => $cost->edition,
            'editions' => $editions,
        ]);
    }

    public function updateCost(Request $request, CostEntry $cost)
    {
        $this->authorize('finances_edit');

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'in:mollie,medailles,overig'],
            'cost_date' => ['required', 'date'],
        ]);

        $cost->update($validated);

        return redirect()->route('intouch.finance.index', ['edition_id' => $cost->edition_id])
            ->with('status', 'Kost bijgewerkt.');
    }

    public function destroyCost(CostEntry $cost)
    {
        $this->authorize('finances_edit');

        $editionId = $cost->edition_id;
        $cost->delete();

        return redirect()->route('intouch.finance.index', ['edition_id' => $editionId])
            ->with('status', 'Kost verwijderd.');
    }

    public function estimateMollieCosts(Request $request)
    {
        $this->authorize('finances_edit');

        $edition = $this->resolveEdition($request);
        if (! $edition) {
            return redirect()->route('intouch.finance.index')->with('error', 'Geen editie geselecteerd.');
        }

        $calculator = new MollieFeeCalculator();
        $totalFee = 0;
        $count = 0;

        $registrations = Registration::query()
            ->where('edition_id', $edition->id)
            ->where('mollie_payment_status', 'paid')
            ->whereNotNull('mollie_payment_id')
            ->get();

        foreach ($registrations as $r) {
            $fee = $calculator->estimateFeeForPayment($r->mollie_payment_id);
            if ($fee !== null) {
                $totalFee += $fee;
                $count++;
            }
        }

        $sponsors = Sponsor::query()
            ->where('edition_id', $edition->id)
            ->where('betaalstatus', 'paid')
            ->whereNotNull('betaling_id')
            ->get();

        foreach ($sponsors as $s) {
            $fee = $calculator->estimateFeeForPayment($s->betaling_id);
            if ($fee !== null) {
                $totalFee += $fee;
                $count++;
            }
        }

        if ($count === 0 && $totalFee == 0) {
            return redirect()->route('intouch.finance.index', ['edition_id' => $edition->id])
                ->with('info', 'Geen betaalde Mollie-betalingen gevonden voor deze editie.');
        }

        $description = 'Mollie transactiekosten (schatting)';
        CostEntry::updateOrCreate(
            [
                'edition_id' => $edition->id,
                'description' => $description,
                'category' => 'mollie',
            ],
            [
                'amount' => round($totalFee, 2),
                'cost_date' => now()->toDateString(),
            ]
        );

        return redirect()->route('intouch.finance.index', ['edition_id' => $edition->id])
            ->with('status', "Mollie-kosten geschat: € " . number_format($totalFee, 2, ',', '.') . " op basis van {$count} betalingen.");
    }

    private function resolveEdition(Request $request): ?Edition
    {
        if ($request->filled('edition_id')) {
            return Edition::query()->find($request->edition_id);
        }

        return Edition::active();
    }
}
