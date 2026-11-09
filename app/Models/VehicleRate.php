<?php

namespace App\Models;

use App\Enums\RentalServiceType;
use Database\Factories\VehicleRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vehicle_id',
    'service_type',
    'price_per_day',
    'includes_driver',
    'includes_fuel',
    'notes',
    'is_active',
])]
class VehicleRate extends Model
{
    /** @use HasFactory<VehicleRateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'service_type' => RentalServiceType::class,
            'price_per_day' => 'integer',
            'includes_driver' => 'boolean',
            'includes_fuel' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
