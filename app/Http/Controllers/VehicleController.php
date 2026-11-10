<?php

namespace App\Http\Controllers;

use App\Enums\CatalogKind;
use App\Models\AdditionalFee;
use App\Models\RentalRequirementGroup;
use App\Services\VehicleCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleCatalog $catalog) {}

    public function index(Request $request): View
    {
        $kind = $this->resolveKind($request);

        $filters = $this->catalog->filters($request->query());

        $vehicles = $this->catalog
            ->applyFilters($this->catalog->withCardData($this->catalog->visible($kind)), $filters)
            ->paginate(12)
            ->withQueryString();

        return view('pages.vehicles.index', [
            'kind' => $kind,
            'vehicles' => $vehicles,
            'filters' => $filters,
            'options' => $this->catalog->filterOptions($kind),
            'hasFilters' => collect($filters)->except('sort')->filter(fn ($v) => filled($v))->isNotEmpty(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $kind = $this->resolveKind($request);

        $vehicle = $this->catalog->visible($kind)
            ->with(['category', 'images', 'activeRates'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = $this->catalog->withCardData($this->catalog->visible($kind))
            ->where('vehicle_category_id', $vehicle->vehicle_category_id)
            ->whereKeyNot($vehicle->getKey())
            ->ordered()
            ->limit(4)
            ->get();

        $requirementGroups = RentalRequirementGroup::query()
            ->active()
            ->ordered()
            ->where(fn (Builder $q) => $q
                ->whereNull('vehicle_category_id')
                ->orWhere('vehicle_category_id', $vehicle->vehicle_category_id))
            ->with(['requirements' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->get()
            ->filter(fn ($group) => $group->requirements->isNotEmpty());

        return view('pages.vehicles.show', [
            'kind' => $kind,
            'vehicle' => $vehicle,
            'related' => $related,
            'requirementGroups' => $requirementGroups,
            'fees' => AdditionalFee::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Jenis katalog diambil dari default route (bukan argumen method), karena Laravel
     * mengisi argumen method berdasarkan urutan parameter route, bukan namanya.
     */
    private function resolveKind(Request $request): CatalogKind
    {
        $kind = CatalogKind::tryFrom((string) $request->route('kind')) ?? abort(404);

        // Fitur motor bisa dimatikan admin; halamannya tidak boleh bisa dibuka.
        abort_unless(VehicleCatalog::kindEnabled($kind), 404);

        return $kind;
    }
}
