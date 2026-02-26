<?php

namespace App\Services;

use Mollie\Laravel\Facades\Mollie;

class MollieFeeCalculator
{
    public function estimateFeeForPayment(string $paymentId): ?float
    {
        try {
            $payment = Mollie::api()->payments->get($paymentId);
        } catch (\Exception $e) {
            return null;
        }

        if ($payment->status !== 'paid') {
            return null;
        }

        $amount = (float) $payment->amount->value;
        $method = strtolower($payment->method ?? 'default');
        $fees = config('mollie_fees.' . $method, config('mollie_fees.default'));

        $fee = $fees['fixed'] ?? 0;
        if (isset($fees['percentage']) && $fees['percentage'] > 0) {
            $fee += ($amount * $fees['percentage']) / 100;
        }

        return round($fee, 2);
    }
}
