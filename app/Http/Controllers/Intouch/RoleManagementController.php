<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $request->user()->canManageRoles() || abort(403);

        $roles = Role::query()
            ->withCount('users')
            ->with('permissions')
            ->orderBy('slug')
            ->get();

        return view('intouch.beheer.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(Request $request)
    {
        $request->user()->canManageRoles() || abort(403);

        $permissions = Permission::query()->orderBy('slug')->get();

        return view('intouch.beheer.roles.create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->user()->canManageRoles() || abort(403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug', 'regex:/^[a-z0-9_]+$/'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('intouch.beheer.roles.index')
            ->with('status', 'Rol aangemaakt.');
    }

    public function edit(Request $request, Role $role)
    {
        $request->user()->canManageRoles() || abort(403);

        $role->load('permissions');
        $permissions = Permission::query()->orderBy('slug')->get();

        return view('intouch.beheer.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->user()->canManageRoles() || abort(403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug,' . $role->id, 'regex:/^[a-z0-9_]+$/'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()
            ->route('intouch.beheer.roles.index')
            ->with('status', 'Rol bijgewerkt.');
    }

    public function destroy(Request $request, Role $role)
    {
        $request->user()->canManageRoles() || abort(403);

        $role->delete();

        return redirect()
            ->route('intouch.beheer.roles.index')
            ->with('status', 'Rol verwijderd.');
    }
}
