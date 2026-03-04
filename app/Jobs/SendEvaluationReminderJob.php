<?php

namespace App\Jobs;

use App\Models\Evaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEvaluationReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $evaluationId,
    ) {}

    public function handle(): void
    {
        $evaluation = Evaluation::find($this->evaluationId);

        if (! $evaluation || ! $evaluation->isSent()) {
            Log::warning('SendEvaluationReminderJob: evaluation not found or not sent', [
                'evaluation_id' => $this->evaluationId,
            ]);

            return;
        }

        $respondedRegistrationIds = $evaluation->responses()->pluck('registration_id');

        $registrations = $evaluation->getTargetRegistrationsQuery()
            ->whereNotIn('id', $respondedRegistrationIds)
            ->get();

        foreach ($registrations as $registration) {
            SendEvaluationInvitationJob::dispatch($evaluation->id, $registration->id);
        }

        if ($registrations->isNotEmpty()) {
            Log::info('SendEvaluationReminderJob: reminder sent', [
                'evaluation_id' => $evaluation->id,
                'count' => $registrations->count(),
            ]);
        }
    }
}
