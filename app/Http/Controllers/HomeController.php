<?php

namespace App\Http\Controllers;

use App\Enums\CatalogKind;
use App\Models\Advantage;
use App\Models\Motto;
use App\Models\VehicleCategory;
use App\Models\WebsiteSetting;
use App\Services\VehicleCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function __invoke(VehicleCatalog $catalog): View
    {
        $featured = $catalog->withCardData($catalog->visible())
            ->featured()
            ->ordered()
            ->limit(6)
            ->get();

        // Kategori yang punya unit tampil; motor hanya jika fiturnya aktif.
        $categories = VehicleCategory::query()
            ->active()
            ->ordered()
            ->when(! VehicleCatalog::motorEnabled(), fn (Builder $q) => $q->where('slug', '!=', VehicleCategory::MOTOR_SLUG))
            ->withCount(['vehicles as published_vehicles_count' => fn (Builder $q) => $q->published()])
            ->get()
            ->filter(fn (VehicleCategory $category) => $category->published_vehicles_count > 0)
            ->values();

        return view('pages.home', [
            'featured' => $featured,
            'categories' => $categories,
            'mottos' => Motto::query()->where('is_active', true)->orderBy('sort_order')->limit(3)->get(),
            'advantages' => Advantage::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'heroTitle' => WebsiteSetting::get('hero_title'),
            'heroSubtitle' => WebsiteSetting::get('hero_subtitle'),
            'heroImage' => $this->heroImageUrl() ?? $featured->first()?->primaryImage?->url(),
            'filters' => [],
            'options' => $catalog->filterOptions(CatalogKind::Car),
        ]);
    }

    private function heroImageUrl(): ?string
    {
        $path = WebsiteSetting::get('hero_image_path');

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
