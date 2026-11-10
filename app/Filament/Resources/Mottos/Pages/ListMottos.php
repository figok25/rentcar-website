<?php

namespace App\Filament\Resources\Mottos\Pages;

use App\Filament\Resources\Mottos\MottoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMottos extends ListRecords
{
    protected static string $resource = MottoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
