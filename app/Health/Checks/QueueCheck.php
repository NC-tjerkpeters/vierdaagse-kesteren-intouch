<?php

namespace App\Health\Checks;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class QueueCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            $connection = config('queue.default');
            $size = 0;

            if ($connection === 'database') {
                $size = DB::table(config('queue.connections.database.table', 'jobs'))->count();
            }
            // sync driver has no queue; redis/database need their driver

            $failed = 0;
            if (Schema::hasTable('failed_jobs')) {
                $failed = DB::table('failed_jobs')->count();
            }

            $summary = $size . ' in wachtrij';
            if ($failed > 0) {
                $summary .= ', ' . $failed . ' mislukt';
            }

            $result->ok()->shortSummary($summary)->meta([
                'queue_size' => $size,
                'failed_jobs' => $failed,
            ]);

            if ($failed > 10) {
                $result->warning($summary . ' – controleer failed jobs.');
            }
        } catch (\Throwable $e) {
            $result->failed('Queue niet controleerbaar: ' . $e->getMessage());
        }

        return $result;
    }
}
