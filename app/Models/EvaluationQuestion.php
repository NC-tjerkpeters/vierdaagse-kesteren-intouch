<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationQuestion extends Model
{
    public const TYPE_RATING = 'rating';

    public const TYPE_NPS = 'nps';

    public const TYPE_CHOICE = 'choice';

    public const TYPE_TEXT = 'text';

    protected $fillable = [
        'evaluation_id',
        'type',
        'question_text',
        'sort_order',
        'options',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class, 'question_id');
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_RATING => 'Beoordeling 1–5',
            self::TYPE_NPS => 'NPS (0–10)',
            self::TYPE_CHOICE => 'Meerkeuze (enkel)',
            self::TYPE_TEXT => 'Open vraag',
        ];
    }
}
