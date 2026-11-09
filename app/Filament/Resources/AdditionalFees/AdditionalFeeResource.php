<?php

namespace App\Filament\Resources\AdditionalFees;

use App\Filament\Resources\AdditionalFees\Pages\CreateAdditionalFee;
use App\Filament\Resources\AdditionalFees\Pages\EditAdditionalFee;
use App\Filament\Resources\AdditionalFees\Pages\ListAdditionalFees;
use App\Filament\Resources\AdditionalFees\Schemas\AdditionalFeeForm;
use App\Filament\Resources\AdditionalFees\Tables\AdditionalFeesTable;
use App\Models\AdditionalFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdditionalFeeResource extends Resource
{
    protected static ?string $model = AdditionalFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $modelLabel = 'Biaya tambahan';

    protected static ?string $pluralModelLabel = 'Biaya tambahan';

    protected static ?string $navigationLabel = 'Biaya tambahan';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdditionalFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdditionalFeesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdditionalFees::route('/'),
            'create' => CreateAdditionalFee::route('/create'),
            'edit' => EditAdditionalFee::route('/{record}/edit'),
        ];
    }
}
