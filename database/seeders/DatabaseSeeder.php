<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Edition;
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
        $roles = [
            ['name' => 'Super beheerder', 'slug' => 'super_admin'],
            ['name' => 'Beheerder', 'slug' => 'admin'],
            ['name' => 'Kijker', 'slug' => 'viewer'],
        ];
        foreach ($roles as $r) {
            Role::query()->firstOrCreate(['slug' => $r['slug']], $r);
        }

        $this->call(PermissionSeeder::class);

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

        $edition = Edition::active() ?? Edition::query()->create([
            'name' => 'Editie ' . date('Y'),
            'start_date' => (date('Y') - 1) . '-10-01',
            'end_date' => date('Y') . '-09-30',
            'is_active' => true,
        ]);

        foreach (['Dag 1', 'Dag 2', 'Dag 3', 'Dag 4'] as $i => $name) {
            EventDay::query()->updateOrCreate(
                ['edition_id' => $edition->id, 'name' => $name],
                [
                    'sort_order' => $i + 1,
                    'is_current' => $i === 0,
                ],
            );
        }
    }
}
