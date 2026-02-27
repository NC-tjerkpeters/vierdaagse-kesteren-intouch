<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inschrijven\RegistrationController;
use App\Http\Controllers\Intouch\Auth\LoginController;
use App\Http\Controllers\Intouch\DashboardController;
use App\Http\Controllers\Intouch\DistanceController;
use App\Http\Controllers\Intouch\RegistrationController as IntouchRegistrationController;
use App\Http\Controllers\Intouch\ScanOverviewController;
use App\Http\Controllers\Intouch\SettingsController;
use App\Http\Controllers\Intouch\AppSettingsController;
use App\Http\Controllers\Intouch\EditionController;
use App\Http\Controllers\Intouch\FinanceController;
use App\Http\Controllers\Intouch\RoleManagementController;
use App\Http\Controllers\Intouch\SponsorController;
use App\Http\Controllers\Intouch\UserManagementController;
use App\Http\Controllers\Scanner\Auth\LoginController as ScannerLoginController;
use App\Http\Controllers\Scanner\ScanController;

Route::domain(config('app.inschrijven_domain'))
    ->group(function () {
        Route::get('/', [RegistrationController::class, 'create'])
            ->name('inschrijven.create');

        Route::post('/', [RegistrationController::class, 'store'])
            ->name('inschrijven.store');

        Route::get('/bedankt/{registration}', [RegistrationController::class, 'thankyou'])
            ->name('inschrijven.thankyou');

        Route::post('/vrienden/aanmelden', [\App\Http\Controllers\Inschrijven\SponsorRegistrationController::class, 'store'])
            ->name('inschrijven.sponsors.store');
    });

Route::post('/webhooks/mollie/sponsors', \App\Http\Controllers\Inschrijven\SponsorWebhookController::class)
    ->name('webhooks.mollie.sponsors');

Route::domain(config('app.intouch_domain'))
    ->name('intouch.')
    ->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('wachtwoord-vergeten', [\App\Http\Controllers\Intouch\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('wachtwoord-vergeten', [\App\Http\Controllers\Intouch\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('wachtwoord-herstellen/{token}', [\App\Http\Controllers\Intouch\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('wachtwoord-herstellen', [\App\Http\Controllers\Intouch\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

        Route::middleware('auth')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::resource('beheer/afstanden', DistanceController::class)->parameters(['afstanden' => 'distance'])->names('beheer.afstanden');
            Route::get('inschrijvingen', [IntouchRegistrationController::class, 'index'])->name('registrations.index');
            Route::get('inschrijvingen/medaille-overzicht', [\App\Http\Controllers\Intouch\MedalOverviewController::class, 'index'])->name('registrations.medal-overview');
            Route::get('inschrijvingen/export', [IntouchRegistrationController::class, 'export'])->name('registrations.export');
            Route::get('inschrijvingen/{registration}', [IntouchRegistrationController::class, 'show'])->name('registrations.show');
            Route::put('inschrijvingen/{registration}/medaille', [IntouchRegistrationController::class, 'updateMedal'])->name('registrations.update-medal');
            Route::resource('sponsors', SponsorController::class)->parameters(['sponsors' => 'sponsor'])->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::get('loopoverzicht', [ScanOverviewController::class, 'index'])->name('scan-overview.index');
            Route::post('loopoverzicht/scanner-login-qr', [ScanOverviewController::class, 'generateScannerLoginToken'])->name('scan-overview.generate-scanner-login');
            Route::post('loopoverzicht/huidige-dag', [ScanOverviewController::class, 'setCurrentDay'])->name('scan-overview.set-current-day');
            Route::get('instellingen', [SettingsController::class, 'edit'])->name('instellingen.edit');
            Route::put('instellingen', [SettingsController::class, 'update'])->name('instellingen.update');
            Route::post('edition/set', [\App\Http\Controllers\Intouch\EditionSelectorController::class, 'set'])->name('edition.set');
            Route::get('beheer/instellingen', [AppSettingsController::class, 'edit'])->name('beheer.instellingen.edit');
            Route::put('beheer/instellingen', [AppSettingsController::class, 'update'])->name('beheer.instellingen.update');
            Route::get('beheer/edities', [EditionController::class, 'index'])->name('beheer.editions.index');
            Route::get('beheer/edities/aanmaken', [EditionController::class, 'create'])->name('beheer.editions.create');
            Route::post('beheer/edities', [EditionController::class, 'store'])->name('beheer.editions.store');
            Route::get('werkgroep/checklist', function () {
                $edition = \App\Models\Edition::current();
                if (!$edition) {
                    return redirect()->route('intouch.dashboard')->with('info', 'Selecteer eerst een editie (rechtsboven in het menu).');
                }
                return redirect()->route('intouch.beheer.editions.checklist', $edition);
            })->name('werkgroep.checklist');
            Route::get('beheer/edities/{edition}/checklist', [\App\Http\Controllers\Intouch\EditionChecklistController::class, 'index'])->name('beheer.editions.checklist');
            Route::put('beheer/edities/{edition}/checklist', [\App\Http\Controllers\Intouch\EditionChecklistController::class, 'update'])->name('beheer.editions.checklist.update');
            Route::post('beheer/edities/{edition}/checklist/items', [\App\Http\Controllers\Intouch\EditionChecklistController::class, 'addItem'])->name('beheer.editions.checklist.add-item');
            Route::post('beheer/edities/{edition}/checklist/init', [\App\Http\Controllers\Intouch\EditionChecklistController::class, 'initFromDefaults'])->name('beheer.editions.checklist.init');
            Route::get('werkgroep/routes', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'index'])->name('walk-routes.index');
            Route::get('werkgroep/routes/aanmaken', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'create'])->name('walk-routes.create');
            Route::post('werkgroep/routes', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'store'])->name('walk-routes.store');
            Route::get('werkgroep/routes/{walkRoute}', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'edit'])->name('walk-routes.edit');
            Route::put('werkgroep/routes/{walkRoute}', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'update'])->name('walk-routes.update');
            Route::delete('werkgroep/routes/{walkRoute}', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'destroy'])->name('walk-routes.destroy');
            Route::delete('werkgroep/routes/{walkRoute}/pdf', [\App\Http\Controllers\Intouch\WalkRouteController::class, 'deletePdf'])->name('walk-routes.delete-pdf');
            Route::get('financien', [FinanceController::class, 'index'])->name('finance.index');
            Route::get('financien/kosten/aanmaken', [FinanceController::class, 'createCost'])->name('finance.cost.create');
            Route::post('financien/kosten', [FinanceController::class, 'storeCost'])->name('finance.cost.store');
            Route::get('financien/kosten/{cost}/bewerken', [FinanceController::class, 'editCost'])->name('finance.cost.edit');
            Route::put('financien/kosten/{cost}', [FinanceController::class, 'updateCost'])->name('finance.cost.update');
            Route::delete('financien/kosten/{cost}', [FinanceController::class, 'destroyCost'])->name('finance.cost.destroy');
            Route::post('financien/mollie-schatten', [FinanceController::class, 'estimateMollieCosts'])->name('finance.estimate-mollie');
            Route::put('financien/startsaldo', [FinanceController::class, 'updateOpeningBalance'])->name('finance.update-opening-balance');
            Route::get('beheer/gebruikers', [UserManagementController::class, 'index'])->name('beheer.users.index');
            Route::get('beheer/gebruikers/aanmaken', [UserManagementController::class, 'create'])->name('beheer.users.create');
            Route::post('beheer/gebruikers', [UserManagementController::class, 'store'])->name('beheer.users.store');
            Route::get('beheer/gebruikers/{user}/bewerken', [UserManagementController::class, 'edit'])->name('beheer.users.edit');
            Route::put('beheer/gebruikers/{user}', [UserManagementController::class, 'update'])->name('beheer.users.update');
            Route::get('beheer/rollen', [RoleManagementController::class, 'index'])->name('beheer.roles.index');
            Route::get('beheer/rollen/aanmaken', [RoleManagementController::class, 'create'])->name('beheer.roles.create');
            Route::post('beheer/rollen', [RoleManagementController::class, 'store'])->name('beheer.roles.store');
            Route::get('beheer/rollen/{role}/bewerken', [RoleManagementController::class, 'edit'])->name('beheer.roles.edit');
            Route::put('beheer/rollen/{role}', [RoleManagementController::class, 'update'])->name('beheer.roles.update');
            Route::delete('beheer/rollen/{role}', [RoleManagementController::class, 'destroy'])->name('beheer.roles.destroy');
        });
    });

Route::domain(config('app.scanner_domain'))
    ->name('scanner.')
    ->group(function () {
        Route::get('login', [ScannerLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [ScannerLoginController::class, 'login']);
        Route::post('logout', [ScannerLoginController::class, 'logout'])->name('logout');

        Route::middleware('auth')->group(function () {
            Route::get('/', [ScanController::class, 'index'])->name('index');
            Route::get('overview', [ScanController::class, 'overviewApi'])->name('overview.api');
            Route::post('scan', [ScanController::class, 'store'])->name('scan');
            Route::post('scan-api', [ScanController::class, 'storeApi'])->name('scan.api');
        });
    });

Route::domain(config('app.routes_domain'))
    ->name('routes.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Routes\PublicRoutesController::class, 'index'])->name('index');
        Route::get('/{walkRoute}', [\App\Http\Controllers\Routes\PublicRoutesController::class, 'show'])->name('show');
    });

Route::get('/', fn () => view('welcome'))->name('home');
