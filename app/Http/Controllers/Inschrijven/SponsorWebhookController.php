<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Services\SponsorReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mollie\Laravel\Facades\Mollie;

class SponsorWebhookController extends Controller
{
    public function __construct(
        protected SponsorReceiptService $receiptService
    ) {}

    public function __invoke(Request $request)
    {
        $paymentId = trim((string) ($request->input('id') ?? ''));

        if ($paymentId === '') {
            return response('No id', 200);
        }

        $sponsor = Sponsor::where('betaling_id', $paymentId)->first();

        if (! $sponsor) {
            return response('Unknown payment', 200);
        }

        try {
            $payment = Mollie::api()->payments->get($paymentId);
        } catch (\Throwable) {
            return response('Mollie fetch failed', 200);
        }

        $sponsor->update(['betaalstatus' => $payment->status]);

        if ($payment->status !== 'paid') {
            return response('Status updated: ' . $payment->status, 200);
        }

        if ($sponsor->invoice_id) {
            return response('Already processed', 200);
        }

        return DB::transaction(function () use ($sponsor) {
            $maxNum = Sponsor::whereNotNull('invoice_id')
                ->where('invoice_id', '!=', '')
                ->get('invoice_id')
                ->max(fn ($s) => (int) preg_replace('/\D/', '', $s->invoice_id));
            $nextId = ($maxNum ?? 0) + 1;
            $newInvoiceId = str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

            $updated = Sponsor::where('betaling_id', $sponsor->betaling_id)
                ->where(function ($q) {
                    $q->whereNull('invoice_id')->orWhere('invoice_id', '');
                })
                ->update(['invoice_id' => $newInvoiceId]);

            if ($updated < 1) {
                return response('Already invoiced', 200);
            }

            $sponsor->refresh();

            try {
                $this->receiptService->sendReceipt($sponsor);
            } catch (\Throwable $e) {
                report($e);
                return response('Mail error', 500);
            }

            return response('OK paid+mailed', 200);
        });
    }
}
