<?php

namespace App\Providers;

use App\Models\Edition;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        View::composer('intouch.layout', function ($view) {
            $view->with('currentEdition', Edition::current());
            $view->with('editionsForSelector', Edition::query()->orderByDesc('start_date')->get());
        });
    }
}
