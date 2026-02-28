<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;
use App\Models\Distance;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\MicrosoftGraphMailService;
use Mollie\Laravel\Facades\Mollie;

class RegistrationController extends Controller
{
    public function create()
    {
        $distances = Distance::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('inschrijven.create', compact('distances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'house_number' => ['required', 'string', 'max:10'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'distance_id' => ['required', 'exists:distances,id'],
            'wants_medal' => ['nullable', 'boolean'],
            'medal_number' => ['nullable', 'integer'],
        ]);

        $validated['wants_medal'] = (bool) $request->boolean('wants_medal');

        $distance = Distance::query()->findOrFail($validated['distance_id']);

        $edition = \App\Models\Edition::active();
        $registration = Registration::create(array_merge($validated, [
            'edition_id' => $edition?->id,
        ]));

        $amountValue = number_format($distance->price, 2, '.', '');

        $paymentData = [
            'amount' => [
                'currency' => 'EUR',
                'value' => $amountValue,
            ],
            'description' => sprintf(
                'Inschrijving Vierdaagse Kesteren - %s %s (%s km)',
                $registration->first_name,
                $registration->last_name,
                $distance->kilometers
            ),
            'redirectUrl' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'inschrijven.thankyou',
                now()->addHours(48),
                ['registration' => $registration]
            ),
            'metadata' => [
                'registration_id' => $registration->id,
            ],
        ];

        if (! app()->environment('local')) {
            $paymentData['webhookUrl'] = route('webhooks.mollie.registrations');
        }

        $payment = Mollie::api()->payments->create($paymentData);

        $registration->update([
            'mollie_payment_id' => $payment->id,
            'mollie_payment_status' => $payment->status,
        ]);

        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function thankyou(Registration $registration, MicrosoftGraphMailService $graphMailService)
    {
        if ($registration->mollie_payment_id) {
            $payment = Mollie::api()->payments->get($registration->mollie_payment_id);

            $registration->update([
                'mollie_payment_status' => $payment->status,
            ]);

            if ($payment->isPaid() && ! $registration->qr_code) {
                $qrCodeData = 'vierdaagsekesteren:' . $registration->id . ':' . Str::uuid()->toString();

                $registration->update([
                    'qr_code' => $qrCodeData,
                    'usage_count' => 1,
                ]);

                $graphMailService->sendRegistrationTicket($registration->fresh('distance'));
            }
        }

        return view('inschrijven.thankyou', [
            'registration' => $registration,
        ]);
    }
}
