<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanupCommunicationAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $attachmentPath,
    ) {}

    public function handle(): void
    {
        if ($this->attachmentPath && Storage::disk('local')->exists($this->attachmentPath)) {
            Storage::disk('local')->delete($this->attachmentPath);
        }
    }
}
