<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteTemplatePoint extends Model
{
    protected $table = 'route_template_points';

    protected $fillable = ['route_template_id', 'name', 'sort_order'];

    public function routeTemplate(): BelongsTo
    {
        return $this->belongsTo(RouteTemplate::class);
    }
}
