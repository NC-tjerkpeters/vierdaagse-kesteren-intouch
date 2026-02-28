<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerRouteAssignment extends Model
{
    protected $table = 'volunteer_route_assignments';

    protected $fillable = ['volunteer_id', 'walk_route_id'];

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }

    public function walkRoute(): BelongsTo
    {
        return $this->belongsTo(WalkRoute::class);
    }
}
