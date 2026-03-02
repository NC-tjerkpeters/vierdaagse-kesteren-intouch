<?php

namespace App\Http\Requests;

use App\Services\AppSettings;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Redirect;

class SponsorRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'bedrijfsnaam' => ['nullable', 'string', 'max:255'],
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20', 'regex:/^[1-9][0-9]{3}\s?[A-Za-z]{2}$/i'],
            'huisnummer' => ['required', 'string', 'max:20'],
            'telefoonnummer' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'email' => ['required', 'email'],
            'bedrag' => ['required', 'numeric', 'min:1'],
        ];

        if (AppSettings::sponsorsPrivacyConsentRequired()) {
            $rules['privacy_consent'] = ['required', 'accepted'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'postcode.regex' => 'Voer een geldige postcode in (bijv. 1234 AB).',
            'telefoonnummer.regex' => 'Voer een geldig telefoonnummer in.',
            'privacy_consent.accepted' => 'U moet akkoord gaan met de privacyverklaring om door te gaan.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            Redirect::route('inschrijven.sponsors.error', [
                'message' => $validator->errors()->first(),
            ])
        );
    }
}
