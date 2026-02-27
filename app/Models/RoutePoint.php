<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePoint extends Model
{
    protected $fillable = ['walk_route_id', 'name', 'sort_order'];

    public function walkRoute(): BelongsTo
    {
        return $this->belongsTo(WalkRoute::class);
    }
}
