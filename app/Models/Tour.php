<?php

namespace App\Models;

use Database\Factories\TourFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'title',
    'slug',
    'short_description',
    'description',
    'price',
    'duration_label',
    'itinerary',
    'included_facilities',
    'excluded_facilities',
    'is_published',
    'is_featured',
    'sort_order',
    'meta_title',
    'meta_description',
])]
class Tour extends Model
{
    /** @use HasFactory<TourFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        // Tabel foto dihapus berantai oleh database; hapus file fisiknya lewat model.
        static::deleting(function (Tour $tour): void {
            $tour->images->each->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(TourImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function publicUrl(): string
    {
        return route('tours.show', $this->slug);
    }

    /**
     * Pastikan tepat satu foto utama (pertama jika admin tidak menandai).
     */
    public function normalizePrimaryImage(): void
    {
        $images = $this->images()->get();

        if ($images->isEmpty()) {
            return;
        }

        $primary = $images->firstWhere('is_primary', true) ?? $images->first();

        foreach ($images as $image) {
            $shouldBePrimary = $image->is($primary);

            if ($image->is_primary !== $shouldBePrimary) {
                $image->forceFill(['is_primary' => $shouldBePrimary])->saveQuietly();
            }
        }
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
