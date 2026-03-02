<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $fillable = [
        'edition_id',
        'name',
        'target',
        'intro_text',
        'thank_you_text',
        'closes_at',
        'sent_at',
        'mail_subject',
        'mail_body',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'closes_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isClosed(): bool
    {
        if ($this->closes_at === null) {
            return false;
        }

        return now()->isAfter($this->closes_at);
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function getTargetRegistrationsQuery()
    {
        $query = Registration::query()
            ->where('edition_id', $this->edition_id)
            ->where('mollie_payment_status', 'paid')
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($this->target === 'all_finished') {
            $query->where('usage_count', '>=', 13);
        }
        if ($this->target === 'medal_only') {
            $query->where('wants_medal', true);
        }

        return $query;
    }
}
