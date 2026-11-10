<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Support\Features;
use Illuminate\Contracts\View\View;

class TourController extends Controller
{
    public function __construct()
    {
        // Fitur dimatikan admin -> halaman tidak ada.
        abort_unless(Features::tours(), 404);
    }

    public function index(): View
    {
        $tours = Tour::query()
            ->published()
            ->with('primaryImage')
            ->orderByDesc('is_featured')
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('pages.tours.index', ['tours' => $tours]);
    }

    public function show(string $slug): View
    {
        $tour = Tour::query()->published()->with('images')->where('slug', $slug)->firstOrFail();

        $related = Tour::query()
            ->published()
            ->with('primaryImage')
            ->whereKeyNot($tour->getKey())
            ->ordered()
            ->limit(3)
            ->get();

        return view('pages.tours.show', ['tour' => $tour, 'related' => $related]);
    }
}
