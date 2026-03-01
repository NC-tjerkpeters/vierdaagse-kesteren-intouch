<?php

namespace App\Health\Checks;

use App\Services\MicrosoftGraphMailService;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class MicrosoftGraphCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        if (! config('services.msgraph.tenant_id') || ! config('services.msgraph.client_id')) {
            return $result->ok()->shortSummary('Niet geconfigureerd');
        }

        try {
            app(MicrosoftGraphMailService::class)->getAccessToken();

            return $result->ok()->shortSummary('Token verkregen');
        } catch (\Throwable $e) {
            return $result->failed('Microsoft Graph: ' . $e->getMessage());
        }
    }
}
