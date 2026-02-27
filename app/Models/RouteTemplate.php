<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

class RouteTemplate extends Model
{
    protected $table = 'route_templates';

    protected $fillable = ['distance_id', 'title', 'description', 'word_path', 'pdf_path', 'sort_order'];

    public function distance(): BelongsTo
    {
        return $this->belongsTo(Distance::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(RouteTemplatePoint::class, 'route_template_id')->orderBy('sort_order');
    }

    public function getWordUrlAttribute(): ?string
    {
        return $this->word_path ? asset('storage/' . $this->word_path) : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    public function storeWord(UploadedFile $file): string
    {
        $this->deleteWord();
        $path = $file->store('route-templates/' . $this->id, 'public');
        $this->update(['word_path' => $path]);
        return $path;
    }

    public function storePdf(UploadedFile $file): string
    {
        $this->deletePdf();
        $path = $file->store('route-templates/' . $this->id, 'public');
        $this->update(['pdf_path' => $path]);
        return $path;
    }

    public function deleteWord(): void
    {
        if ($this->word_path && \Storage::disk('public')->exists($this->word_path)) {
            \Storage::disk('public')->delete($this->word_path);
        }
        $this->update(['word_path' => null]);
    }

    public function deletePdf(): void
    {
        if ($this->pdf_path && \Storage::disk('public')->exists($this->pdf_path)) {
            \Storage::disk('public')->delete($this->pdf_path);
        }
        $this->update(['pdf_path' => null]);
    }
}
