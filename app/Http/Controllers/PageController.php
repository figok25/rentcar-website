<?php

namespace App\Http\Controllers;

use App\Models\RentalRequirementGroup;
use App\Models\WebsiteSetting;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function requirements(): View
    {
        $groups = RentalRequirementGroup::query()
            ->active()
            ->ordered()
            ->with(['category', 'requirements' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->get()
            ->filter(fn ($group) => $group->requirements->isNotEmpty())
            ->values();

        return view('pages.requirements', ['groups' => $groups]);
    }

    public function contact(): View
    {
        $maps = WebsiteSetting::get('maps_embed_url');

        return view('pages.contact', [
            // Hanya embed resmi Google Maps yang boleh dipasang di iframe.
            'mapsUrl' => $maps && str_starts_with($maps, 'https://www.google.com/maps/embed') ? $maps : null,
        ]);
    }
}
