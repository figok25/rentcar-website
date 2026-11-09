<?php

namespace App\Filament\Resources\AdditionalFees\Pages;

use App\Filament\Resources\AdditionalFees\AdditionalFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdditionalFees extends ListRecords
{
    protected static string $resource = AdditionalFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
