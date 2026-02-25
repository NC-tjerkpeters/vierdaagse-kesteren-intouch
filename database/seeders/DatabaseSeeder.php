<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Distance;
use App\Models\EventDay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Gebruikers beheren', 'slug' => 'manage_users'],
            ['name' => 'Rollen beheren', 'slug' => 'manage_roles'],
        ];
        foreach ($permissions as $p) {
            Permission::query()->firstOrCreate(['slug' => $p['slug']], $p);
        }

        $roles = [
            ['name' => 'Super beheerder', 'slug' => 'super_admin'],
            ['name' => 'Beheerder', 'slug' => 'admin'],
            ['name' => 'Kijker', 'slug' => 'viewer'],
        ];
        foreach ($roles as $r) {
            $role = Role::query()->firstOrCreate(['slug' => $r['slug']], $r);
            if ($role->slug === 'super_admin') {
                $role->permissions()->syncWithoutDetaching(Permission::whereIn('slug', ['manage_users', 'manage_roles'])->pluck('id'));
            }
            if ($role->slug === 'admin') {
                $role->permissions()->syncWithoutDetaching(Permission::where('slug', 'manage_users')->pluck('id'));
            }
        }

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@vierdaagsekesteren.nl'],
            [
                'name' => 'Intouch Admin',
                'password' => bcrypt('wijzig-dit-wachtwoord'),
            ],
        );
        if (!$admin->roles()->where('slug', 'admin')->exists()) {
            $admin->roles()->attach(Role::where('slug', 'admin')->first()->id);
        }

        Distance::query()->updateOrCreate(
            ['name' => '2,5 km'],
            [
                'kilometers' => 2.5,
                'price' => 3.35,
                'is_active' => true,
                'sort_order' => 1,
                'event_day_sort_orders' => [2, 4],
            ],
        );

        Distance::query()->updateOrCreate(
            ['name' => '5 km'],
            [
                'kilometers' => 5.0,
                'price' => 5.75,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        Distance::query()->updateOrCreate(
            ['name' => '10 km'],
            [
                'kilometers' => 10.0,
                'price' => 5.75,
                'is_active' => true,
                'sort_order' => 3,
            ],
        );

        foreach (['Dag 1', 'Dag 2', 'Dag 3', 'Dag 4'] as $i => $name) {
            EventDay::query()->updateOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $i + 1,
                    'is_current' => $i === 0,
                ],
            );
        }
    }
}
