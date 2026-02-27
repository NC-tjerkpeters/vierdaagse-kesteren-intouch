<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = config('permissions.all', []);

        foreach ($permissions as $p) {
            Permission::query()->updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => ($p['group'] ?? '') ? "{$p['group']} – {$p['name']}" : $p['name'],
                    'group' => $p['group'] ?? null,
                ]
            );
        }

        // Standaard rol-permissies
        $allPermissionIds = Permission::query()->pluck('id');

        $superAdmin = Role::query()->where('slug', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync($allPermissionIds);
        }

        $admin = Role::query()->where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(
                Permission::whereIn('slug', [
                    'dashboard_view', 'afstanden_view', 'afstanden_create', 'afstanden_edit', 'afstanden_delete',
                    'inschrijvingen_view', 'inschrijvingen_edit', 'inschrijvingen_export', 'inschrijvingen_medal_overview',
                    'loopoverzicht_view',
                    'sponsors_view', 'sponsors_create', 'sponsors_edit', 'sponsors_delete',
                    'manage_users', 'manage_roles', 'instellingen_edit', 'editions_manage', 'finances_view', 'finances_edit',
                    'routes_view', 'routes_manage', 'checklist_view',
                ])->pluck('id')
            );
        }

        $viewer = Role::query()->where('slug', 'viewer')->first();
        if ($viewer) {
            $viewer->permissions()->sync(
                Permission::whereIn('slug', [
                    'dashboard_view', 'afstanden_view', 'inschrijvingen_view', 'inschrijvingen_medal_overview', 'loopoverzicht_view',
                    'sponsors_view', 'instellingen_edit', 'finances_view',
                ])->pluck('id')
            );
        }
    }
}
