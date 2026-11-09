<?php

namespace App\Models;

use Database\Factories\TravelServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'slug',
    'short_description',
    'description',
    'route_label',
    'vehicle_information',
    'price_label',
    'terms',
    'is_published',
    'sort_order',
    'meta_title',
    'meta_description',
])]
class TravelService extends Model
{
    /** @use HasFactory<TravelServiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
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
