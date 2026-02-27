<?php

namespace App\Http\Controllers\Scanner\Auth;

use App\Http\Controllers\Controller;
use App\Models\ScannerLoginToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('scanner.index');
        }

        $token = $request->query('token');
        if ($token) {
            $loginToken = ScannerLoginToken::findByToken($token);
            if ($loginToken) {
                $scannerUser = User::query()->where('email', 'scanner@vierdaagsekesteren.nl')->first();
                if ($scannerUser) {
                    Auth::login($scannerUser);
                    $request->session()->regenerate();
                    return redirect()->route('scanner.index');
                }
            }
            return redirect()->route('scanner.login')->with('error', 'De QR-code is verlopen of ongeldig. Vraag een nieuwe code aan de organisator.');
        }

        return view('scanner.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('scanner.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('scanner.login');
    }
}
