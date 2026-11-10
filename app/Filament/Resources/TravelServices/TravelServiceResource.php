<?php

namespace App\Filament\Resources\TravelServices;

use App\Filament\Resources\TravelServices\Pages\CreateTravelService;
use App\Filament\Resources\TravelServices\Pages\EditTravelService;
use App\Filament\Resources\TravelServices\Pages\ListTravelServices;
use App\Filament\Resources\TravelServices\Schemas\TravelServiceForm;
use App\Filament\Resources\TravelServices\Tables\TravelServicesTable;
use App\Models\TravelService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TravelServiceResource extends Resource
{
    protected static ?string $model = TravelService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan';

    protected static ?string $modelLabel = 'Layanan travel';

    protected static ?string $pluralModelLabel = 'Layanan travel';

    protected static ?string $navigationLabel = 'Layanan travel';

    protected static ?string $slug = 'travel';

    protected static ?int $navigationSort = 21;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TravelServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelServicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTravelServices::route('/'),
            'create' => CreateTravelService::route('/create'),
            'edit' => EditTravelService::route('/{record}/edit'),
        ];
    }
}
