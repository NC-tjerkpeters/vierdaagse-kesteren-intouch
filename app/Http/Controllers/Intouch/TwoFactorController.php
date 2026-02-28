<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function setup(TwoFactorService $twoFactor)
    {
        $user = Auth::user();
        $secret = $twoFactor->generateSecret();
        session(['two_factor_setup_secret' => $secret]);

        return view('intouch.two-factor.setup', [
            'user' => $user,
            'qrCodeUrl' => $twoFactor->getQrCodeUrl($user, $secret),
            'secret' => $secret,
        ]);
    }

    public function confirm(Request $request, TwoFactorService $twoFactor)
    {
        $secret = session('two_factor_setup_secret');
        if (! $secret) {
            return redirect()->route('intouch.instellingen.edit')
                ->with('error', 'Sessie verlopen. Start twee-factor authenticatie opnieuw.');
        }

        $request->validate(['code' => ['required', 'string', 'size:6']]);

        if (! $twoFactor->verify($secret, $request->code)) {
            return redirect()->back()->with('error', 'Ongeldige code. Probeer opnieuw.');
        }

        $user = Auth::user();
        $user->two_factor_secret = encrypt($secret);
        $user->two_factor_recovery_codes = $twoFactor->generateRecoveryCodes();
        $user->save();

        session()->forget('two_factor_setup_secret');

        return view('intouch.two-factor.recovery-codes', [
            'recoveryCodes' => $user->two_factor_recovery_codes,
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => ['required']]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $request->password,
        ])) {
            return redirect()->back()->with('error', 'Onjuist wachtwoord.');
        }

        $user = Auth::user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return redirect()->route('intouch.instellingen.edit')
            ->with('status', 'Twee-factor authenticatie uitgeschakeld.');
    }
}
