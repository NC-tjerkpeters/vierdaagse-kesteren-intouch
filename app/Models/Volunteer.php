<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Volunteer extends Model
{
    protected $fillable = [
        'edition_id',
        'name',
        'email',
        'phone',
        'notes',
        'can_regulate_traffic',
    ];

    protected function casts(): array
    {
        return [
            'can_regulate_traffic' => 'boolean',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(VolunteerSlot::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(VolunteerAvailability::class);
    }

    public function isAvailableOnEventDay(int $eventDayId): bool
    {
        return $this->availabilities()->where('event_day_id', $eventDayId)->exists();
    }

    public function routeAssignments(): HasMany
    {
        return $this->hasMany(VolunteerRouteAssignment::class);
    }
}
