<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Services\SponsorReceiptService;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('sponsors_view');

        $filter = strtolower(trim($request->get('status', 'all')));
        $allowed = ['all', 'paid', 'open', 'failed', 'canceled', 'expired', 'pending', 'authorized'];
        if (!in_array($filter, $allowed, true)) {
            $filter = 'all';
        }

        $query = Sponsor::query()->forActiveEdition();

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
                    ->orWhere('postcode', 'like', "%{$q}%")
                    ->orWhere('telefoonnummer', 'like', "%{$q}%")
                    ->orWhere('betaling_id', 'like', "%{$q}%");
            });
        }

        $sponsors = $query->orderByRaw("(betaalstatus = 'paid') DESC")
            ->orderByDesc('invoice_id')
            ->orderByDesc('id')
            ->paginate(25);

        $doelbedrag = \App\Services\AppSettings::sponsorsDoelbedrag();
        $totaalOpgehaald = (float) Sponsor::query()->forActiveEdition()->where('betaalstatus', 'paid')->sum('bedrag');
        $aantalBetaald = Sponsor::query()->forActiveEdition()->where('betaalstatus', 'paid')->count();
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
        $this->authorize('sponsors_create');

        return view('intouch.sponsors.create');
    }

    public function store(Request $request)
    {
        $this->authorize('sponsors_create');

        $validated = $request->validate([
            'bedrijfsnaam' => ['nullable', 'string', 'max:255'],
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'huisnummer' => ['nullable', 'string', 'max:20'],
            'telefoonnummer' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email'],
            'bedrag' => ['required', 'numeric', 'min:0'],
            'betaalstatus' => ['required', 'string', 'in:paid,open,pending,authorized,failed,canceled,expired'],
        ], [
            'voornaam.required' => 'Voornaam is verplicht.',
            'achternaam.required' => 'Achternaam is verplicht.',
            'email.required' => 'E-mail is verplicht.',
            'bedrag.required' => 'Bedrag is verplicht.',
        ]);

        $edition = \App\Models\Edition::active();
        Sponsor::create(array_merge($validated, [
            'edition_id' => $edition?->id,
        ]));

        return redirect()->route('intouch.sponsors.index')
            ->with('status', 'Sponsor toegevoegd.');
    }

    public function edit(Sponsor $sponsor)
    {
        $this->authorize('sponsors_edit');

        $sponsor = Sponsor::query()->forActiveEdition()->findOrFail($sponsor->id);

        return view('intouch.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $this->authorize('sponsors_edit');

        $sponsor = Sponsor::query()->forActiveEdition()->findOrFail($sponsor->id);

        $validated = $request->validate([
            'bedrijfsnaam' => ['nullable', 'string', 'max:255'],
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'huisnummer' => ['nullable', 'string', 'max:20'],
            'telefoonnummer' => ['nullable', 'string', 'max:30'],
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
        $this->authorize('sponsors_delete');

        $sponsor = Sponsor::query()->forActiveEdition()->findOrFail($sponsor->id);
        $sponsor->delete();

        return redirect()->route('intouch.sponsors.index')
            ->with('status', 'Sponsor verwijderd.');
    }

    public function resendReceipt(Sponsor $sponsor, SponsorReceiptService $receiptService)
    {
        $this->authorize('sponsors_edit');

        $sponsor = Sponsor::query()->forActiveEdition()->findOrFail($sponsor->id);

        if ($sponsor->betaalstatus !== 'paid') {
            return redirect()->back()->with('error', 'Kwitantie kan alleen worden verstuurd naar betaalde sponsors.');
        }

        if (empty($sponsor->invoice_id)) {
            return redirect()->back()->with('error', 'Deze sponsor heeft nog geen kwitantienummer. Wacht op de verwerking van de betaling.');
        }

        try {
            $receiptService->sendReceipt($sponsor);

            return redirect()->back()->with('status', 'Kwitantie opnieuw verstuurd naar ' . $sponsor->email . '.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Verzenden mislukt: ' . $e->getMessage());
        }
    }
}
