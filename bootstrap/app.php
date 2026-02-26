<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['webhooks/mollie/sponsors', 'vrienden/aanmelden']);
        $middleware->redirectTo(guests: function (\Illuminate\Http\Request $request) {
            $host = $request->getHost();
            $intouchDomain = config('app.intouch_domain');
            $scannerDomain = config('app.scanner_domain');
            if ($host === $intouchDomain || str_contains($host, 'intouch')) {
                return route('intouch.login');
            }
            if ($host === $scannerDomain || str_contains($host, 'scanner')) {
                return route('scanner.login');
            }
            return url('/');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
