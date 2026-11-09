<?php

namespace App\Filament\Widgets;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehicleStatusOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Status kendaraan';

    protected function getStats(): array
    {
        $counts = Vehicle::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [];

        foreach (VehicleStatus::cases() as $status) {
            $stats[] = Stat::make($status->label(), (int) ($counts[$status->value] ?? 0))
                ->color($status->color());
        }

        return $stats;
    }
}
