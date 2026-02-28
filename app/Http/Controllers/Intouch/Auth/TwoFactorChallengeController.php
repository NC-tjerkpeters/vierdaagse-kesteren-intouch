<?php

namespace App\Http\Controllers\Intouch\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorChallengeController extends Controller
{
    public function show()
    {
        if (! session('login.id')) {
            return redirect()->route('intouch.login');
        }

        return view('intouch.auth.two-factor-challenge');
    }

    public function verify(Request $request, TwoFactorService $twoFactor)
    {
        $userId = session('login.id');
        if (! $userId) {
            return redirect()->route('intouch.login');
        }

        $request->validate([
            'code' => ['required_without:recovery_code', 'nullable', 'string', 'size:6'],
            'recovery_code' => ['required_without:code', 'nullable', 'string'],
        ]);

        $user = User::find($userId);
        if (! $user) {
            session()->forget(['login.id']);
            return redirect()->route('intouch.login')->with('error', 'Sessie verlopen. Log opnieuw in.');
        }

        $verified = false;

        if ($request->filled('code')) {
            $secret = decrypt($user->two_factor_secret);
            $verified = $twoFactor->verify($secret, $request->code);
        } elseif ($request->filled('recovery_code')) {
            $verified = $twoFactor->verifyRecoveryCode($user, $request->recovery_code);
        }

        if (! $verified) {
            return redirect()->back()->with('error', 'Ongeldige code. Probeer opnieuw.');
        }

        session()->forget('login.id');
        Auth::login($user, session('login.remember', false));
        session()->forget('login.remember');
        $request->session()->regenerate();

        return redirect()->intended(route('intouch.dashboard'));
    }
}
