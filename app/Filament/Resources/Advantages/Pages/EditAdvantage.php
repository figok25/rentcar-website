<?php

namespace App\Filament\Resources\Advantages\Pages;

use App\Filament\Resources\Advantages\AdvantageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvantage extends EditRecord
{
    protected static string $resource = AdvantageResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
