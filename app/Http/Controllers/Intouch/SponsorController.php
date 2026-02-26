<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    public function index(Request $request)
    {
        $filter = strtolower(trim($request->get('status', 'all')));
        $allowed = ['all', 'paid', 'open', 'failed', 'canceled', 'expired', 'pending', 'authorized'];
        if (!in_array($filter, $allowed, true)) {
            $filter = 'all';
        }

        $query = Sponsor::query();

        if ($filter !== 'all') {
            $query->where('betaalstatus', $filter);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('bedrijfsnaam', 'like', "%{$q}%")
                    ->orWhere('voornaam', 'like', "%{$q}%")
                    ->orWhere('achternaam', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('betaling_id', 'like', "%{$q}%");
            });
        }

        $sponsors = $query->orderByRaw("(betaalstatus = 'paid') DESC")
            ->orderByDesc('invoice_id')
            ->orderByDesc('id')
            ->paginate(25);

        $doelbedrag = config('sponsors.doelbedrag', 1850);
        $totaalOpgehaald = (float) Sponsor::where('betaalstatus', 'paid')->sum('bedrag');
        $aantalBetaald = Sponsor::where('betaalstatus', 'paid')->count();
        $progress = $doelbedrag > 0 ? min(100, ($totaalOpgehaald / $doelbedrag) * 100) : 0;
        $nogNodig = max(0, $doelbedrag - $totaalOpgehaald);

        return view('intouch.sponsors.index', [
            'sponsors' => $sponsors,
            'filter' => $filter,
            'totaalOpgehaald' => $totaalOpgehaald,
            'aantalBetaald' => $aantalBetaald,
            'doelbedrag' => $doelbedrag,
            'progress' => $progress,
            'nogNodig' => $nogNodig,
        ]);
    }

    public function create()
    {
        return view('intouch.sponsors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bedrijfsnaam' => ['nullable', 'string', 'max:255'],
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'bedrag' => ['required', 'numeric', 'min:0'],
            'betaalstatus' => ['required', 'string', 'in:paid,open,pending,authorized,failed,canceled,expired'],
        ], [
            'voornaam.required' => 'Voornaam is verplicht.',
            'achternaam.required' => 'Achternaam is verplicht.',
            'email.required' => 'E-mail is verplicht.',
            'bedrag.required' => 'Bedrag is verplicht.',
        ]);

        Sponsor::create($validated);

        return redirect()->route('intouch.sponsors.index')
            ->with('status', 'Sponsor toegevoegd.');
    }

    public function edit(Sponsor $sponsor)
    {
        return view('intouch.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $validated = $request->validate([
            'bedrijfsnaam' => ['nullable', 'string', 'max:255'],
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'bedrag' => ['required', 'numeric', 'min:0'],
            'betaalstatus' => ['required', 'string', 'in:paid,open,pending,authorized,failed,canceled,expired'],
        ], [
            'voornaam.required' => 'Voornaam is verplicht.',
            'achternaam.required' => 'Achternaam is verplicht.',
            'email.required' => 'E-mail is verplicht.',
            'bedrag.required' => 'Bedrag is verplicht.',
        ]);

        $sponsor->update($validated);

        return redirect()->route('intouch.sponsors.index')
            ->with('status', 'Sponsor bijgewerkt.');
    }

    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();

        return redirect()->route('intouch.sponsors.index')
            ->with('status', 'Sponsor verwijderd.');
    }
}
