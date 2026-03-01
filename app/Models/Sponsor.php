<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    protected $table = 'sponsors';

    protected $fillable = [
        'edition_id',
        'bedrijfsnaam',
        'voornaam',
        'achternaam',
        'postcode',
        'huisnummer',
        'telefoonnummer',
        'email',
        'bedrag',
        'betaalstatus',
        'invoice_id',
        'betaling_id',
        'privacy_consent_at',
    ];

    protected function casts(): array
    {
        return [
            'bedrag' => 'decimal:2',
            'privacy_consent_at' => 'datetime',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function scopeForActiveEdition($query)
    {
        $edition = Edition::current();
        if ($edition) {
            return $query->where('edition_id', $edition->id);
        }
        return $query;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->bedrijfsnaam ?: trim($this->voornaam . ' ' . $this->achternaam);
    }
}
