<?php

namespace App\Models;

use Database\Factories\TourImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['tour_id', 'image_path', 'alt_text', 'is_primary', 'sort_order'])]
class TourImage extends Model
{
    /** @use HasFactory<TourImageFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (TourImage $image): void {
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
