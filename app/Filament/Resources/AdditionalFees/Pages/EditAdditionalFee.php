<?php

namespace App\Filament\Resources\AdditionalFees\Pages;

use App\Filament\Resources\AdditionalFees\AdditionalFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdditionalFee extends EditRecord
{
    protected static string $resource = AdditionalFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
