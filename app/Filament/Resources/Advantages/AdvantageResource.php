<?php

namespace App\Filament\Resources\Advantages;

use App\Filament\Resources\Advantages\Pages\CreateAdvantage;
use App\Filament\Resources\Advantages\Pages\EditAdvantage;
use App\Filament\Resources\Advantages\Pages\ListAdvantages;
use App\Filament\Resources\Advantages\Schemas\AdvantageForm;
use App\Filament\Resources\Advantages\Tables\AdvantagesTable;
use App\Models\Advantage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdvantageResource extends Resource
{
    protected static ?string $model = Advantage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Keunggulan';

    protected static ?string $pluralModelLabel = 'Keunggulan';

    protected static ?string $navigationLabel = 'Keunggulan';

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AdvantageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvantagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdvantages::route('/'),
            'create' => CreateAdvantage::route('/create'),
            'edit' => EditAdvantage::route('/{record}/edit'),
        ];
    }
}
