<?php

namespace App\Health\Checks;

use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class MollieWebhookCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        if (app()->environment('local')) {
            return $result->ok()->shortSummary('Overgeslagen (lokaal)');
        }

        $baseUrl = config('app.url');
        $fails = [];

        $endpoints = [
            'Inschrijvingen' => $baseUrl . '/webhooks/mollie/registrations',
            'Sponsors' => $baseUrl . '/webhooks/mollie/sponsors',
        ];

        foreach ($endpoints as $label => $url) {
            try {
                $response = Http::timeout(5)->asForm()->post($url, ['id' => 'health-check']);

                if (! $response->successful()) {
                    $fails[] = "{$label}: HTTP {$response->status()}";
                }
            } catch (\Throwable $e) {
                $fails[] = "{$label}: {$e->getMessage()}";
            }
        }

        if (! empty($fails)) {
            return $result->failed(implode('. ', $fails));
        }

        return $result->ok()->shortSummary('Endpoints bereikbaar');
    }
}
