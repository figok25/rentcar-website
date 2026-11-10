<?php

namespace App\Filament\Resources\TravelServices\Pages;

use App\Filament\Resources\TravelServices\TravelServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelServices extends ListRecords
{
    protected static string $resource = TravelServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
