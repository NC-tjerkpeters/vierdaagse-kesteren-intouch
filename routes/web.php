<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inschrijven\RegistrationController;
use App\Http\Controllers\Intouch\Auth\LoginController;
use App\Http\Controllers\Intouch\DashboardController;
use App\Http\Controllers\Intouch\DistanceController;
use App\Http\Controllers\Intouch\RegistrationController as IntouchRegistrationController;
use App\Http\Controllers\Intouch\ScanOverviewController;
use App\Http\Controllers\Intouch\SettingsController;
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
            Route::resource('afstanden', DistanceController::class)->parameters(['afstanden' => 'distance']);
            Route::get('inschrijvingen', [IntouchRegistrationController::class, 'index'])->name('registrations.index');
            Route::get('inschrijvingen/export', [IntouchRegistrationController::class, 'export'])->name('registrations.export');
            Route::get('inschrijvingen/{registration}', [IntouchRegistrationController::class, 'show'])->name('registrations.show');
            Route::get('loopoverzicht', [ScanOverviewController::class, 'index'])->name('scan-overview.index');
            Route::resource('sponsors', SponsorController::class)->parameters(['sponsors' => 'sponsor'])->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::post('loopoverzicht/huidige-dag', [ScanOverviewController::class, 'setCurrentDay'])->name('scan-overview.set-current-day');
            Route::get('instellingen', [SettingsController::class, 'edit'])->name('instellingen.edit');
            Route::put('instellingen', [SettingsController::class, 'update'])->name('instellingen.update');
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

Route::get('/', fn () => view('welcome'))->name('home');
