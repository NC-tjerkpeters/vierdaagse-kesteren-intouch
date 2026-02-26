<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostEntry extends Model
{
    protected $fillable = [
        'edition_id',
        'description',
        'amount',
        'payment_method',
        'category',
        'cost_date',
    ];

    public static function paymentMethods(): array
    {
        return [
            'bank' => 'Bank',
            'kas' => 'Kas',
        ];
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cost_date' => 'date',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public static function categories(): array
    {
        return [
            'mollie' => 'Mollie transactiekosten',
            'medailles' => 'Medailles',
            'overig' => 'Overig',
        ];
    }
}
