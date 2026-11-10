<?php

namespace App\Support;

use App\Enums\VehicleStatus;
use App\Models\Faq;
use App\Models\Tour;
use App\Models\TravelService;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pembuat data terstruktur schema.org (JSON-LD). Hanya field yang terisi
 * yang ikut ditulis, dan tidak ada rating/ulasan buatan sendiri.
 */
class StructuredData
{
    /**
     * @param  array<string, mixed>  $site  data situs dari binding public.site
     * @return array<string, mixed>
     */
    public static function business(array $site): array
    {
        $logo = $site['logo'] ? Storage::disk('public')->url($site['logo']) : null;

        return self::clean([
            '@type' => 'AutoRental',
            '@id' => url('/').'#bisnis',
            'name' => $site['name'],
            'url' => url('/'),
            'telephone' => $site['phone'] ?: null,
            'image' => $logo,
            'logo' => $logo,
            'address' => $site['address'] ? ['@type' => 'PostalAddress', 'streetAddress' => $site['address'], 'addressCountry' => 'ID'] : null,
            'sameAs' => array_values(array_filter([$site['instagram'] ?? null, $site['facebook'] ?? null])) ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public static function website(string $name): array
    {
        return ['@type' => 'WebSite', 'name' => $name, 'url' => url('/'), 'inLanguage' => 'id-ID'];
    }

    /**
     * @param  Collection<int, Faq>  $faqs
     * @return array<string, mixed>|null
     */
    public static function faq(Collection $faqs): ?array
    {
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $items  pasangan [nama, url]
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    public static function vehicle(Vehicle $vehicle): array
    {
        $rates = $vehicle->activeRates;
        $prices = $rates->pluck('price_per_day')->filter(fn ($p) => $p !== null);

        $offers = null;
        if ($prices->isNotEmpty()) {
            $offers = [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'IDR',
                'lowPrice' => $prices->min(),
                'highPrice' => $prices->max(),
                'offerCount' => $prices->count(),
                'availability' => $vehicle->status === VehicleStatus::Available
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $vehicle->publicUrl(),
            ];
        }

        return self::clean([
            '@type' => 'Product',
            'name' => $vehicle->name,
            'description' => Str::limit(strip_tags((string) ($vehicle->description ?: $vehicle->name)), 300),
            'url' => $vehicle->publicUrl(),
            'image' => $vehicle->images->map(fn ($image) => $image->url())->values()->all() ?: null,
            'brand' => $vehicle->brand ? ['@type' => 'Brand', 'name' => $vehicle->brand] : null,
            'category' => $vehicle->category?->name,
            'offers' => $offers,
        ]);
    }

    /** @return array<string, mixed> */
    public static function tour(Tour $tour): array
    {
        return self::clean([
            '@type' => 'TouristTrip',
            'name' => $tour->title,
            'description' => Str::limit(strip_tags((string) ($tour->short_description ?: $tour->description ?: $tour->title)), 300),
            'url' => $tour->publicUrl(),
            'image' => $tour->images->map(fn ($image) => $image->url())->values()->all() ?: null,
            'offers' => $tour->price !== null ? [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => $tour->price,
                'url' => $tour->publicUrl(),
            ] : null,
        ]);
    }

    /** @return array<string, mixed> */
    public static function travel(TravelService $service): array
    {
        return self::clean([
            '@type' => 'Service',
            'serviceType' => 'Travel',
            'name' => $service->title,
            'description' => Str::limit(strip_tags((string) ($service->short_description ?: $service->description ?: $service->title)), 300),
            'url' => $service->publicUrl(),
            'areaServed' => $service->route_label ?: null,
        ]);
    }

    /**
     * Gabungkan beberapa node menjadi satu dokumen @graph.
     *
     * @param  array<int, array<string, mixed>|null>  $nodes
     * @return array<string, mixed>
     */
    public static function graph(array $nodes): array
    {
        return ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($nodes))];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function clean(array $data): array
    {
        return array_filter($data, fn ($value) => $value !== null && $value !== '' && $value !== []);
    }
}
