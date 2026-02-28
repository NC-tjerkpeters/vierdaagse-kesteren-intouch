<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mollie\Laravel\Facades\Mollie;

class RegistrationWebhookController extends Controller
{
    public function __construct(
        protected MicrosoftGraphMailService $mailService
    ) {}

    public function __invoke(Request $request)
    {
        $paymentId = trim((string) ($request->input('id') ?? ''));

        if ($paymentId === '') {
            return response('No id', 200);
        }

        $registration = Registration::where('mollie_payment_id', $paymentId)->first();

        if (! $registration) {
            return response('Unknown payment', 200);
        }

        try {
            $payment = Mollie::api()->payments->get($paymentId);
        } catch (\Throwable) {
            return response('Mollie fetch failed', 200);
        }

        $registration->update(['mollie_payment_status' => $payment->status]);

        if ($payment->status !== 'paid') {
            return response('Status updated: ' . $payment->status, 200);
        }

        if ($registration->qr_code) {
            return response('Already processed', 200);
        }

        $qrCodeData = 'vierdaagsekesteren:' . $registration->id . ':' . Str::uuid()->toString();

        $registration->update([
            'qr_code' => $qrCodeData,
            'usage_count' => 1,
        ]);

        try {
            $this->mailService->sendRegistrationTicket($registration->fresh('distance'));
        } catch (\Throwable $e) {
            report($e);

            return response('Mail error', 500);
        }

        return response('OK paid+ticket sent', 200);
    }
}
