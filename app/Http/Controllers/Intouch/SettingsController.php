<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit()
    {
        $this->authorize('instellingen_edit');

        return view('intouch.instellingen.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('instellingen_edit');

        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()
            ->route('intouch.instellingen.edit')
            ->with('status', 'Instellingen opgeslagen.');
    }
}
