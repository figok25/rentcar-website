<?php

namespace App\Http\Controllers;

use App\Models\TravelService;
use App\Support\Features;
use Illuminate\Contracts\View\View;

class TravelController extends Controller
{
    public function __construct()
    {
        abort_unless(Features::travel(), 404);
    }

    public function index(): View
    {
        return view('pages.travel.index', [
            'services' => TravelService::query()->published()->ordered()->paginate(12)->withQueryString(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = TravelService::query()->published()->where('slug', $slug)->firstOrFail();

        $related = TravelService::query()
            ->published()
            ->whereKeyNot($service->getKey())
            ->ordered()
            ->limit(3)
            ->get();

        return view('pages.travel.show', ['service' => $service, 'related' => $related]);
    }
}
