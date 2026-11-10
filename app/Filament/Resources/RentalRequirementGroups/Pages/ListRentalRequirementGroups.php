<?php

namespace App\Filament\Resources\RentalRequirementGroups\Pages;

use App\Filament\Resources\RentalRequirementGroups\RentalRequirementGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalRequirementGroups extends ListRecords
{
    protected static string $resource = RentalRequirementGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
