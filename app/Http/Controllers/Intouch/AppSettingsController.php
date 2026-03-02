<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AppSettings;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    public function edit()
    {
        $this->authorize('instellingen_edit');

        return view('intouch.beheer.instellingen.edit', [
            'sponsorsDoelbedrag' => AppSettings::sponsorsDoelbedrag(),
            'sponsorsPrivacyConsentRequired' => AppSettings::sponsorsPrivacyConsentRequired(),
            'mollieFees' => AppSettings::mollieFeesAll(),
            'scannerMinMinutes' => AppSettings::scannerMinMinutes(),
            'scannerPointNames' => AppSettings::scannerPointNames(),
            'noodnummers' => AppSettings::appNoodnummers(),
            'mollieFeeMethods' => array_keys(config('mollie_fees', [])),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('instellingen_edit');

        $rules = [
            'sponsors_doelbedrag' => ['nullable', 'numeric', 'min:0'],
            'scanner_min_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'app_noodnummers' => ['nullable', 'string', 'max:500'],
        ];

        foreach (config('mollie_fees', []) as $method => $_) {
            $rules["mollie_fee_{$method}_percentage"] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules["mollie_fee_{$method}_fixed"] = ['nullable', 'numeric', 'min:0'];
        }

        for ($i = 1; $i <= 13; $i++) {
            $rules["scanner_point_{$i}"] = ['nullable', 'string', 'max:100'];
        }

        $data = $request->validate($rules);

        Setting::set('sponsors.doelbedrag', (float) ($data['sponsors_doelbedrag'] ?? config('sponsors.doelbedrag', 1850)));
        Setting::set('sponsors.privacy_consent_required', (bool) ($data['sponsors_privacy_consent_required'] ?? true));

        $fees = [];
        foreach (config('mollie_fees', []) as $method => $default) {
            $pct = $data["mollie_fee_{$method}_percentage"] ?? $default['percentage'] ?? 0;
            $fix = $data["mollie_fee_{$method}_fixed"] ?? $default['fixed'] ?? 0;
            $fees[$method] = ['percentage' => (float) $pct, 'fixed' => (float) $fix];
        }
        Setting::set('mollie_fees', $fees);

        Setting::set('scanner.min_minutes_between_scans', (int) ($data['scanner_min_minutes'] ?? 5));

        $pointNames = [];
        for ($i = 1; $i <= 13; $i++) {
            $pointNames[$i] = $data["scanner_point_{$i}"] ?? config("scanner.point_names.{$i}", "Punt {$i}");
        }
        Setting::set('scanner.point_names', $pointNames);

        Setting::set('app.noodnummers', $data['app_noodnummers'] ?? config('app.noodnummers', ''));

        return redirect()
            ->route('intouch.beheer.instellingen.edit')
            ->with('status', 'Instellingen opgeslagen.');
    }
}
