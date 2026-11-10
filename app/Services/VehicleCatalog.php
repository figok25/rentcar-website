<?php

namespace App\Services;

use App\Enums\CatalogKind;
use App\Enums\TransmissionType;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\WebsiteSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Aturan "apa yang boleh tampil di publik" dan filter katalog, dipusatkan di sini
 * supaya beranda, katalog, dan detail memakai aturan yang sama.
 */
class VehicleCatalog
{
    public const SORTS = ['featured', 'price_asc', 'price_desc'];

    public const CAPACITY_OPTIONS = [2, 4, 6, 10];

    public static function motorEnabled(): bool
    {
        return WebsiteSetting::bool('feature_motor_enabled', false);
    }

    public static function kindEnabled(CatalogKind $kind): bool
    {
        return $kind !== CatalogKind::Motor || self::motorEnabled();
    }

    /**
     * Kendaraan yang dipublikasikan, kategorinya aktif, dan (untuk motor)
     * fiturnya diaktifkan. Status unit TIDAK memengaruhi tampil/tidaknya.
     */
    public function visible(?CatalogKind $kind = null): Builder
    {
        $query = Vehicle::query()
            ->published()
            ->whereHas('category', fn (Builder $q) => $q->where('is_active', true));

        if ($kind !== null) {
            return $this->ofKind($query, $kind);
        }

        // Tanpa kind: gabungan, tapi motor hanya jika fiturnya aktif.
        if (! self::motorEnabled()) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', '!=', VehicleCategory::MOTOR_SLUG));
        }

        return $query;
    }

    private function ofKind(Builder $query, CatalogKind $kind): Builder
    {
        if (! self::kindEnabled($kind)) {
            return $query->whereRaw('1 = 0');
        }

        $operator = $kind === CatalogKind::Motor ? '=' : '!=';

        return $query->whereHas('category', fn (Builder $q) => $q->where('slug', $operator, VehicleCategory::MOTOR_SLUG));
    }

    public function withCardData(Builder $query): Builder
    {
        return $query
            ->with(['category', 'primaryImage'])
            ->withMin('activeRates as starting_price', 'price_per_day');
    }

    /**
     * Ambil hanya parameter filter yang valid; yang tidak valid dibuang diam-diam
     * (halaman katalog tidak boleh error hanya karena query string salah).
     *
     * @return array<string, mixed>
     */
    public function filters(array $query): array
    {
        return Validator::make($query, [
            'q' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:80'],
            'brand' => ['nullable', 'string', 'max:80'],
            'transmission' => ['nullable', 'in:'.implode(',', array_column(TransmissionType::cases(), 'value'))],
            'min_capacity' => ['nullable', 'integer', 'min:1', 'max:60'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'sort' => ['nullable', 'in:'.implode(',', self::SORTS)],
        ])->valid();
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['q'] ?? null)) {
            $term = '%'.addcslashes($filters['q'], '%_\\').'%';
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', $term)
                ->orWhere('brand', 'like', $term)
                ->orWhere('model', 'like', $term));
        }

        if (filled($filters['category'] ?? null)) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $filters['category']));
        }

        if (filled($filters['brand'] ?? null)) {
            $query->where('brand', $filters['brand']);
        }

        if (filled($filters['transmission'] ?? null)) {
            $query->where('transmission', $filters['transmission']);
        }

        if (filled($filters['min_capacity'] ?? null)) {
            $query->where('passenger_capacity', '>=', (int) $filters['min_capacity']);
        }

        if (filled($filters['min_price'] ?? null) || filled($filters['max_price'] ?? null)) {
            $query->whereHas('activeRates', function (Builder $q) use ($filters) {
                if (filled($filters['min_price'] ?? null)) {
                    $q->where('price_per_day', '>=', (int) $filters['min_price']);
                }
                if (filled($filters['max_price'] ?? null)) {
                    $q->where('price_per_day', '<=', (int) $filters['max_price']);
                }
            });
        }

        return $this->applySort($query, $filters['sort'] ?? 'featured');
    }

    private function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderByRaw('starting_price is null')->orderBy('starting_price')->orderBy('name'),
            'price_desc' => $query->orderByRaw('starting_price is null')->orderByDesc('starting_price')->orderBy('name'),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name'),
        };
    }

    /**
     * Pilihan untuk form filter pada katalog sesuai jenisnya.
     *
     * @return array{categories: Collection, brands: Collection}
     */
    public function filterOptions(CatalogKind $kind): array
    {
        $categories = VehicleCategory::query()
            ->active()
            ->ordered()
            ->where('slug', $kind === CatalogKind::Motor ? '=' : '!=', VehicleCategory::MOTOR_SLUG)
            ->whereHas('vehicles', fn (Builder $q) => $q->published())
            ->get();

        $brands = $this->visible($kind)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return compact('categories', 'brands');
    }
}
