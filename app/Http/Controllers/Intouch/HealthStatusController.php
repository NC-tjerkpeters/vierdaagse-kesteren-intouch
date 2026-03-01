<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

class HealthStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('manage_users');

        $request->query->set('fresh', true);

        return app(HealthCheckResultsController::class)($request, app(\Spatie\Health\ResultStores\ResultStore::class), app(\Spatie\Health\Health::class));
    }
}
