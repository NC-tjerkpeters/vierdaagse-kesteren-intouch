<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    public function eventDays(): HasMany
    {
        return $this->hasMany(EventDay::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->first();
    }

    public static function activate(self $edition): void
    {
        static::query()->update(['is_active' => false]);
        $edition->update(['is_active' => true]);
    }
}
