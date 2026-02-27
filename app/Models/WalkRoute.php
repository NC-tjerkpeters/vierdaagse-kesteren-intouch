<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

class WalkRoute extends Model
{
    protected $table = 'walk_routes';

    protected $fillable = ['edition_id', 'route_template_id', 'distance_id', 'title', 'description', 'pdf_path', 'sort_order', 'event_day_sort_orders'];

    protected function casts(): array
    {
        return [
            'event_day_sort_orders' => 'array',
        ];
    }

    /** Of deze route op de gegeven dag actief is. Leeg/null = alle dagen. */
    public function runsOnEventDay(\App\Models\EventDay $day): bool
    {
        $allowed = $this->event_day_sort_orders;
        if ($allowed === null || $allowed === []) {
            return true;
        }

        return in_array((int) $day->sort_order, array_map('intval', $allowed), true);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function routeTemplate(): BelongsTo
    {
        return $this->belongsTo(RouteTemplate::class);
    }

    public function distance(): BelongsTo
    {
        return $this->belongsTo(Distance::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(RoutePoint::class, 'walk_route_id')->orderBy('sort_order');
    }

    public function getPdfUrlAttribute(): ?string
    {
        if (! $this->pdf_path) {
            return null;
        }

        return asset('storage/' . $this->pdf_path);
    }

    public function storePdf(UploadedFile $file): string
    {
        $path = $file->store('route-pdfs/' . $this->edition_id, 'public');

        return $path;
    }

    public function deletePdf(): void
    {
        if ($this->pdf_path && \Storage::disk('public')->exists($this->pdf_path)) {
            \Storage::disk('public')->delete($this->pdf_path);
        }
        $this->update(['pdf_path' => null]);
    }
}
