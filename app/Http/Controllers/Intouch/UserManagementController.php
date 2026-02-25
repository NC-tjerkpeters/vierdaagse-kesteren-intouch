<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $request->user()->canManageUsers() || abort(403);

        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        $roles = Role::query()->orderBy('slug')->get();

        return view('intouch.beheer.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create(Request $request)
    {
        $request->user()->canManageUsers() || abort(403);

        $roles = Role::query()->orderBy('slug')->get();

        return view('intouch.beheer.users.create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->user()->canManageUsers() || abort(403);

        $canManageRoles = $request->user()->canManageRoles();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        if ($canManageRoles) {
            $rules['roles'] = ['array'];
            $rules['roles.*'] = ['exists:roles,id'];
        }

        $data = $request->validate($rules);

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->save();

        if ($canManageRoles && ! empty($data['roles'] ?? [])) {
            $user->roles()->sync($data['roles']);
        }

        return redirect()
            ->route('intouch.beheer.users.index')
            ->with('status', 'Gebruiker aangemaakt.');
    }

    public function edit(Request $request, User $user)
    {
        $request->user()->canManageUsers() || abort(403);

        $roles = Role::query()->orderBy('slug')->get();
        $user->load('roles');

        return view('intouch.beheer.users.edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->user()->canManageUsers() || abort(403);

        $canManageRoles = $request->user()->canManageRoles();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }
        if ($canManageRoles) {
            $rules['roles'] = ['array'];
            $rules['roles.*'] = ['exists:roles,id'];
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'] ?? null)) {
            $user->password = $data['password'];
        }
        $user->save();

        if ($canManageRoles && array_key_exists('roles', $data)) {
            $user->roles()->sync($data['roles'] ?? []);
        }

        return redirect()
            ->route('intouch.beheer.users.index')
            ->with('status', 'Gebruiker bijgewerkt.');
    }
}
