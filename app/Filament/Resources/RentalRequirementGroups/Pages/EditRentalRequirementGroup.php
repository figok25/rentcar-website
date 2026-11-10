<?php

namespace App\Filament\Resources\RentalRequirementGroups\Pages;

use App\Filament\Resources\RentalRequirementGroups\RentalRequirementGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalRequirementGroup extends EditRecord
{
    protected static string $resource = RentalRequirementGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
