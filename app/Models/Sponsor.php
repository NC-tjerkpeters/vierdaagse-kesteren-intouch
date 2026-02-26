<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $table = 'sponsors';

    protected $fillable = [
        'bedrijfsnaam',
        'voornaam',
        'achternaam',
        'email',
        'bedrag',
        'betaalstatus',
        'invoice_id',
        'betaling_id',
    ];

    protected function casts(): array
    {
        return [
            'bedrag' => 'decimal:2',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->bedrijfsnaam ?: trim($this->voornaam . ' ' . $this->achternaam);
    }
}
