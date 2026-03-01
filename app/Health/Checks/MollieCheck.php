<?php

namespace App\Health\Checks;

use Mollie\Laravel\Facades\Mollie;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class MollieCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            Mollie::api()->methods->all();

            return $result->ok()->shortSummary('Verbonden');
        } catch (\Throwable $e) {
            return $result->failed('Mollie API niet bereikbaar: ' . $e->getMessage());
        }
    }
}
