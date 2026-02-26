<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;

class SponsorRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bedrijfsnaam' => ['nullable', 'string', 'max:255'],
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20', 'regex:/^[1-9][0-9]{3}\s?[A-Za-z]{2}$/i'],
            'huisnummer' => ['required', 'string', 'max:20'],
            'telefoonnummer' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'email' => ['required', 'email'],
            'bedrag' => ['required', 'numeric', 'min:1'],
        ], [
            'postcode.regex' => 'Voer een geldige postcode in (bijv. 1234 AB).',
            'telefoonnummer.regex' => 'Voer een geldig telefoonnummer in.',
        ]);

        $bedrag = (float) str_replace(',', '.', (string) $validated['bedrag']);
        if ($bedrag < 0.01) {
            return back()->withErrors(['bedrag' => 'Bedrag moet groter zijn dan 0.'])->withInput();
        }
        $amountValue = number_format($bedrag, 2, '.', '');

        $edition = \App\Models\Edition::active();
        $sponsor = Sponsor::create([
            'bedrijfsnaam' => $validated['bedrijfsnaam'] ?: null,
            'voornaam' => $validated['voornaam'],
            'achternaam' => $validated['achternaam'],
            'postcode' => strtoupper(preg_replace('/\s+/', ' ', trim($validated['postcode']))),
            'huisnummer' => $validated['huisnummer'],
            'telefoonnummer' => $validated['telefoonnummer'],
            'email' => $validated['email'],
            'bedrag' => $amountValue,
            'betaalstatus' => 'open',
            'edition_id' => $edition?->id,
        ]);

        $webhookUrl = config('sponsors.webhook_url') ?? (config('app.url') . config('sponsors.webhook_path'));

        $paymentData = [
            'amount' => [
                'currency' => 'EUR',
                'value' => $amountValue,
            ],
            'description' => 'Vrienden van de Vierdaagse Kesteren',
            'redirectUrl' => config('sponsors.redirect_url'),
            'metadata' => ['sponsor_id' => $sponsor->id],
        ];

        if (! app()->environment('local')) {
            $paymentData['webhookUrl'] = $webhookUrl;
        }

        $payment = Mollie::api()->payments->create($paymentData);

        $sponsor->update([
            'betaling_id' => $payment->id,
            'betaalstatus' => $payment->status,
        ]);

        return redirect($payment->getCheckoutUrl(), 303);
    }
}
