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
        'opening_balance',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'opening_balance' => 'decimal:2',
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

    /** Huidige editie in beeld: session (archief) of actieve editie. */
    public static function current(): ?self
    {
        $sessionId = session('edition_id');
        if ($sessionId) {
            $edition = static::query()->find($sessionId);
            if ($edition) {
                return $edition;
            }
        }
        return static::active();
    }

    public static function activate(self $edition): void
    {
        static::query()->update(['is_active' => false]);
        $edition->update(['is_active' => true]);
    }

    /** Eindsaldo: startsaldo + opbrengsten - kosten. Gebruikt bij overdracht naar volgende editie. */
    public function getClosingBalanceAttribute(): float
    {
        $revenue = $this->registrations()
            ->where('mollie_payment_status', 'paid')
            ->with('distance')
            ->get()
            ->sum(fn ($r) => (float) ($r->distance->price ?? 0));
        $revenue += $this->sponsors()->where('betaalstatus', 'paid')->sum('bedrag');
        $costs = $this->costEntries()->sum('amount');

        return (float) $this->opening_balance + $revenue - $costs;
    }

    public function costEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CostEntry::class);
    }
}
