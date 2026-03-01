<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;
use App\Http\Requests\SponsorRegistrationRequest;
use App\Models\Sponsor;
use Mollie\Laravel\Facades\Mollie;

class SponsorRegistrationController extends Controller
{
    public function store(SponsorRegistrationRequest $request)
    {
        $validated = $request->validated();

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
            'privacy_consent_at' => now(),
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
