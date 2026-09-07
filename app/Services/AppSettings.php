<?php

namespace App\Services;

use App\Models\Setting;

class AppSettings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Setting::get($key, '__none__');

        if ($value === '__none__') {
            return config($key, $default);
        }

        return $value;
    }

    public static function sponsorsDoelbedrag(): float
    {
        return (float) static::get('sponsors.doelbedrag', config('sponsors.doelbedrag', 1850));
    }

    public static function sponsorsPrivacyConsentRequired(): bool
    {
        $val = static::get('sponsors.privacy_consent_required', '__none__');

        return $val === '__none__' ? (bool) config('sponsors.privacy_consent_required', true) : (bool) $val;
    }

    public static function mollieFees(string $method = 'default'): array
    {
        $all = static::get('mollie_fees');
        if ($all === null || ! is_array($all)) {
            return config('mollie_fees.' . $method, config('mollie_fees.default'));
        }

        return $all[$method] ?? $all['default'] ?? config('mollie_fees.default');
    }

    public static function mollieFeesAll(): array
    {
        $all = static::get('mollie_fees');
        if ($all === null || ! is_array($all)) {
            return config('mollie_fees', []);
        }

        return $all;
    }

    public static function scannerMinMinutes(): int
    {
        return (int) static::get('scanner.min_minutes_between_scans', config('scanner.min_minutes_between_scans', 5));
    }

    public static function scannerPointNames(): array
    {
        $names = static::get('scanner.point_names');
        if (! is_array($names)) {
            return config('scanner.point_names', []);
        }

        return $names;
    }

    public static function appNoodnummers(): string
    {
        return (string) static::get('app.noodnummers', config('app.noodnummers', '06 52 44 16 10, 06 40 89 37 40'));
    }

    public static function registrationsOpen(): bool
    {
        $val = static::get('inschrijving.open', '__none__');

        return $val === '__none__' ? true : (bool) $val;
    }

    public static function registrationsClosedMessage(): string
    {
        $default = 'De inschrijving voor de Vierdaagse Kesteren is gesloten. Bedankt voor je interesse!';
        $val = static::get('inschrijving.closed_message', '__none__');

        if ($val === '__none__' || $val === null || $val === '') {
            return $default;
        }

        return (string) $val;
    }
}
