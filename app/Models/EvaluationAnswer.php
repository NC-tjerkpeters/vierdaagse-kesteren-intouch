<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationAnswer extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'value',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(EvaluationResponse::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class);
    }
}
