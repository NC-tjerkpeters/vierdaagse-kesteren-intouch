<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantEmailLog extends Model
{
    protected $table = 'participant_email_logs';

    protected $fillable = [
        'edition_id',
        'template_key',
        'subject',
        'recipient_filter',
        'recipient_count',
        'sent_count',
        'failed_count',
        'sent_by',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_filter' => 'array',
            'recipient_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
