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
        'category',
        'cost_date',
    ];

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
