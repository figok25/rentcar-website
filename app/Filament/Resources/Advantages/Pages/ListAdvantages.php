<?php

namespace App\Filament\Resources\Advantages\Pages;

use App\Filament\Resources\Advantages\AdvantageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdvantages extends ListRecords
{
    protected static string $resource = AdvantageResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
