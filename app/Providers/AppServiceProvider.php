<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Gates voor alle permissions uit config
        $permissions = config('permissions.all', []);
        foreach ($permissions as $p) {
            $slug = $p['slug'];
            Gate::define($slug, fn ($user) => $user->hasPermission($slug));
        }

    }
}
