<?php

namespace App\Providers;

use App\Health\Checks\MollieCheck;
use App\Health\Checks\MollieWebhookCheck;
use App\Health\Checks\MicrosoftGraphCheck;
use App\Models\Edition;
use App\Models\ParticipantEmailTemplate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Health::checks([
            DatabaseCheck::new(),
            CacheCheck::new(),
            MollieCheck::new()->name('mollie')->label('Mollie API'),
            MollieWebhookCheck::new()->name('mollie_webhook')->label('Mollie webhooks'),
            MicrosoftGraphCheck::new()->name('microsoft_graph')->label('Microsoft Graph (e-mail)'),
        ]);

        Route::bind('template', fn ($value) => ParticipantEmailTemplate::findOrFail($value));

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
