<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(
        protected Google2FA $google2fa
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrCodeUrl(User $user, string $secret): string
    {
        $companyName = config('app.name', 'Vierdaagse Kesteren');
        $holder = $user->email;
        $otpauthUrl = $this->google2fa->getQRCodeUrl($companyName, $holder, $secret);

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauthUrl);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code, 1);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        return $codes;
    }

    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $code = strtoupper(str_replace(' ', '', $code));
        $index = array_search($code, array_map(fn ($c) => strtoupper(str_replace(' ', '', $c)), $codes), true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->two_factor_recovery_codes = array_values($codes);
        $user->save();

        return true;
    }
}
