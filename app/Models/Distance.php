<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distance extends Model
{
    protected $fillable = [
        'name',
        'kilometers',
        'price',
        'is_active',
        'sort_order',
        'event_day_sort_orders',
    ];

    protected function casts(): array
    {
        return [
            'event_day_sort_orders' => 'array',
        ];
    }

    /** Of deze afstand op de gegeven avond loopt. Leeg/null = alle avonden. */
    public function runsOnEventDay(\App\Models\EventDay $day): bool
    {
        $allowed = $this->event_day_sort_orders;
        if ($allowed === null || $allowed === []) {
            return true;
        }

        return in_array((int) $day->sort_order, array_map('intval', $allowed), true);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
