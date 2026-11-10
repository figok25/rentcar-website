<?php

namespace App\Filament\Resources\Mottos\Pages;

use App\Filament\Resources\Mottos\MottoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMotto extends EditRecord
{
    protected static string $resource = MottoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
