<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventDay extends Model
{
    protected $fillable = [
        'edition_id',
        'name',
        'is_current',
        'sort_order',
        'event_date',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'event_date' => 'date',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function scopeForActiveEdition($query)
    {
        $edition = Edition::active();
        if ($edition) {
            return $query->where('edition_id', $edition->id);
        }
        return $query;
    }

    public static function getCurrent(): ?self
    {
        $active = Edition::active();
        if (! $active) {
            return static::query()->where('is_current', true)->first();
        }
        return static::query()
            ->where('edition_id', $active->id)
            ->where('is_current', true)
            ->first();
    }

    /** Punten die bij deze avond horen: Dag 1 = 1–4, Dag 2 = 5–7, Dag 3 = 8–10, Dag 4 = 11–13 */
    public function allowedPointNumbers(): array
    {
        $s = (int) $this->sort_order;
        if ($s === 1) {
            return [1, 2, 3, 4];
        }
        $start = ($s - 1) * 3 + 2;
        $end = $s * 3 + 1;

        return range($start, $end);
    }

    /** Start-, post- en finishpunt voor deze avond: Dag 1 = 2,3,4; Dag 2 = 5,6,7; etc. */
    public function startPostFinishPointNumbers(): array
    {
        $s = (int) $this->sort_order;
        if ($s === 1) {
            return ['start' => 2, 'post' => 3, 'finish' => 4];
        }
        $base = ($s - 1) * 3 + 2;

        return ['start' => $base, 'post' => $base + 1, 'finish' => $base + 2];
    }
}
