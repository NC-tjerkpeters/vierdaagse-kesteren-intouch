<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'postal_code',
        'house_number',
        'phone_number',
        'email',
        'distance_id',
        'wants_medal',
        'medal_number',
        'mollie_payment_id',
        'mollie_payment_status',
        'qr_code',
        'usage_count',
        'last_scan_at',
    ];

    protected function casts(): array
    {
        return [
            'last_scan_at' => 'datetime',
        ];
    }

    public function distance(): BelongsTo
    {
        return $this->belongsTo(Distance::class);
    }
}
