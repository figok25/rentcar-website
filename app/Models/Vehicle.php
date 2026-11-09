<?php

namespace App\Models;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'vehicle_category_id',
    'name',
    'slug',
    'brand',
    'model',
    'year',
    'transmission',
    'fuel_type',
    'passenger_capacity',
    'description',
    'status',
    'is_featured',
    'is_published',
    'sort_order',
    'meta_title',
    'meta_description',
])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        // Hapus permanen kendaraan -> hapus juga file fotonya.
        static::forceDeleting(function (Vehicle $vehicle): void {
            $vehicle->images->each->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'passenger_capacity' => 'integer',
            'transmission' => TransmissionType::class,
            'fuel_type' => FuelType::class,
            'status' => VehicleStatus::class,
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(VehicleImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(VehicleRate::class);
    }

    public function activeRates(): HasMany
    {
        return $this->hasMany(VehicleRate::class)->where('is_active', true);
    }

    /**
     * Hanya kendaraan yang boleh tampil di frontend.
     * Status unit (tersedia, disewa, dst.) tidak memengaruhi publikasi.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeWithStatus(Builder $query, VehicleStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * Pastikan tepat satu foto utama: jika tidak ada, foto pertama dipilih;
     * jika lebih dari satu, hanya yang pertama yang dipertahankan.
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
}
