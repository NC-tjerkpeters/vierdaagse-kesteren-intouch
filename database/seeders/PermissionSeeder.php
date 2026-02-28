<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Maakt permissies aan/ bij vanuit config. Overschrijft GEEN rol-permissies;
     * die beheer je via Beheer → Rollen.
     *
     * Alleen bij een verse installatie (rol heeft nog geen permissies) worden
     * standaardrechten gezet.
     */
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

        // Alleen standaard permissies zetten als de rol nog geen heeft (verse installatie)
        $syncIfEmpty = function (Role $role, array $slugs): void {
            if ($role->permissions()->count() > 0) {
                return;
            }
            $role->permissions()->sync(
                Permission::whereIn('slug', $slugs)->pluck('id')
            );
        };

        $superAdmin = Role::query()->where('slug', 'super_admin')->first();
        if ($superAdmin) {
            $syncIfEmpty($superAdmin, Permission::query()->pluck('slug')->toArray());
        }

        $admin = Role::query()->where('slug', 'admin')->first();
        if ($admin) {
            $syncIfEmpty($admin, [
                'dashboard_view', 'afstanden_view', 'afstanden_create', 'afstanden_edit', 'afstanden_delete',
                'inschrijvingen_view', 'inschrijvingen_edit', 'inschrijvingen_export', 'inschrijvingen_medal_overview',
                'communicatie_view', 'communicatie_send', 'communicatie_templates',
                'loopoverzicht_view',
                'sponsors_view', 'sponsors_create', 'sponsors_edit', 'sponsors_delete',
                'manage_users', 'manage_roles', 'instellingen_edit', 'editions_manage', 'finances_view', 'finances_edit',
                'routes_view', 'routes_manage', 'checklist_view',
                'vrijwilligers_view', 'vrijwilligers_manage',
            ]);
        }

        $viewer = Role::query()->where('slug', 'viewer')->first();
        if ($viewer) {
            $syncIfEmpty($viewer, [
                'dashboard_view', 'afstanden_view', 'inschrijvingen_view', 'inschrijvingen_medal_overview', 'communicatie_view',
                'loopoverzicht_view', 'sponsors_view', 'instellingen_edit', 'finances_view', 'vrijwilligers_view',
            ]);
        }

        $werkgroep = Role::query()->firstOrCreate(
            ['slug' => 'werkgroep'],
            ['name' => 'Werkgroep']
        );
        $syncIfEmpty($werkgroep, [
            'dashboard_view',
            'finances_view', 'finances_edit',
            'checklist_view',
            'routes_view', 'routes_manage',
            'communicatie_view', 'communicatie_send', 'communicatie_templates',
            'vrijwilligers_view', 'vrijwilligers_manage',
        ]);

        // Nieuwe permissies toevoegen aan bestaande rollen (zonder andere rechten te verwijderen)
        $extraPermissions = Permission::whereIn('slug', ['communicatie_view', 'communicatie_send', 'communicatie_templates', 'vrijwilligers_view', 'vrijwilligers_manage'])->pluck('id');
        foreach ([$admin, $superAdmin, $werkgroep] as $role) {
            if ($role && $role->permissions()->count() > 0) {
                $role->permissions()->syncWithoutDetaching($extraPermissions);
            }
        }
        if ($viewer) {
            $viewer->permissions()->syncWithoutDetaching(
                Permission::whereIn('slug', ['communicatie_view', 'vrijwilligers_view'])->pluck('id')
            );
        }
    }
}
