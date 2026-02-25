<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::query()->with('distance');

        if ($request->filled('distance_id')) {
            $query->where('distance_id', $request->distance_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->where('mollie_payment_status', 'paid');
            }
            if ($request->status === 'open') {
                $query->where('mollie_payment_status', '!=', 'paid');
            }
        }
        if ($request->filled('medal')) {
            if ($request->medal === 'yes') {
                $query->where('wants_medal', true);
            }
            if ($request->medal === 'no') {
                $query->where('wants_medal', false);
            }
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $registrations = $query->orderByDesc('created_at')->paginate(25);

        $distances = \App\Models\Distance::query()->orderBy('sort_order')->get(['id', 'name']);

        return view('intouch.inschrijvingen.index', [
            'registrations' => $registrations,
            'distances' => $distances,
        ]);
    }

    public function show(Registration $registration)
    {
        $registration->load('distance');

        return view('intouch.inschrijvingen.show', compact('registration'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Registration::query()->with('distance');

        if ($request->filled('distance_id')) {
            $query->where('distance_id', $request->distance_id);
        }
        if ($request->filled('status') && $request->status === 'paid') {
            $query->where('mollie_payment_status', 'paid');
        }
        if ($request->filled('medal') && $request->medal === 'yes') {
            $query->where('wants_medal', true);
        }

        $registrations = $query->orderBy('last_name')->orderBy('first_name')->get();

        $filename = 'inschrijvingen-vierdaagse-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($registrations) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID', 'Voornaam', 'Achternaam', 'Postcode', 'Huisnummer', 'Telefoon', 'E-mail',
                'Afstand', 'Medaille', 'Medaillenummer', 'Status betaling', 'Inschrijfdatum',
            ]);
            foreach ($registrations as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->first_name,
                    $r->last_name,
                    $r->postal_code,
                    $r->house_number,
                    $r->phone_number,
                    $r->email,
                    $r->distance->name ?? '',
                    $r->wants_medal ? 'Ja' : 'Nee',
                    $r->medal_number ?? '',
                    $r->mollie_payment_status,
                    $r->created_at->format('d-m-Y H:i'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
