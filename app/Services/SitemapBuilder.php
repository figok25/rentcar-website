<?php

namespace App\Services;

use App\Models\Tour;
use App\Models\TravelService;
use App\Support\Features;
use Illuminate\Support\Carbon;

/**
 * Daftar URL publik untuk sitemap.xml. Hanya halaman yang benar-benar
 * bisa dibuka pengunjung: unit/paket yang dipublikasikan dan fitur yang aktif.
 */
class SitemapBuilder
{
    public function __construct(private readonly VehicleCatalog $catalog) {}

    /**
     * @return array<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function entries(): array
    {
        $entries = [
            $this->entry(route('home'), null, 'weekly', '1.0'),
            $this->entry(route('vehicles.index'), null, 'daily', '0.9'),
        ];

        if (VehicleCatalog::motorEnabled()) {
            $entries[] = $this->entry(route('motor.index'), null, 'daily', '0.8');
        }

        if (Features::tours()) {
            $entries[] = $this->entry(route('tours.index'), null, 'weekly', '0.8');
        }

        if (Features::travel()) {
            $entries[] = $this->entry(route('travel.index'), null, 'weekly', '0.7');
        }

        $entries[] = $this->entry(route('requirements'), null, 'monthly', '0.5');
        $entries[] = $this->entry(route('contact'), null, 'monthly', '0.5');

        foreach ($this->catalog->visible()->with('category')->orderBy('id')->get() as $vehicle) {
            $entries[] = $this->entry($vehicle->publicUrl(), $vehicle->updated_at, 'weekly', '0.8');
        }

        if (Features::tours()) {
            foreach (Tour::query()->published()->orderBy('id')->get() as $tour) {
                $entries[] = $this->entry($tour->publicUrl(), $tour->updated_at, 'monthly', '0.7');
            }
        }

        if (Features::travel()) {
            foreach (TravelService::query()->published()->orderBy('id')->get() as $service) {
                $entries[] = $this->entry($service->publicUrl(), $service->updated_at, 'monthly', '0.6');
            }
        }

        return $entries;
    }

    public function xml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($this->entries() as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
            if ($entry['lastmod']) {
                $xml .= '    <lastmod>'.$entry['lastmod']."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$entry['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$entry['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        return $xml.'</urlset>'."\n";
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string, priority: string}
     */
    private function entry(string $loc, mixed $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod ? Carbon::parse($lastmod)->toDateString() : null,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
