<?php

namespace App\Http\Controllers\Intouch\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('intouch.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $throttleKey = 'password-reset:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            throw ValidationException::withMessages([
                'email' => ['Te veel pogingen. Probeer het over een minuut opnieuw.'],
            ]);
        }

        $status = Password::sendResetLink($request->only('email'));

        RateLimiter::hit($throttleKey);

        if ($status === Password::RESET_LINK_SENT) {
            RateLimiter::clear($throttleKey);
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
