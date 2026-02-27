<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditionChecklistItem extends Model
{
    protected $fillable = ['edition_id', 'title', 'sort_order', 'is_done', 'note'];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
