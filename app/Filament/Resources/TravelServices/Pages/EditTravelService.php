<?php

namespace App\Filament\Resources\TravelServices\Pages;

use App\Filament\Resources\TravelServices\TravelServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelService extends EditRecord
{
    protected static string $resource = TravelServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
