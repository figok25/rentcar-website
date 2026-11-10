<?php

namespace App\Filament\Resources\RentalRequirementGroups;

use App\Filament\Resources\RentalRequirementGroups\Pages\CreateRentalRequirementGroup;
use App\Filament\Resources\RentalRequirementGroups\Pages\EditRentalRequirementGroup;
use App\Filament\Resources\RentalRequirementGroups\Pages\ListRentalRequirementGroups;
use App\Filament\Resources\RentalRequirementGroups\Schemas\RentalRequirementGroupForm;
use App\Filament\Resources\RentalRequirementGroups\Tables\RentalRequirementGroupsTable;
use App\Models\RentalRequirementGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RentalRequirementGroupResource extends Resource
{
    protected static ?string $model = RentalRequirementGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Kelompok syarat sewa';

    protected static ?string $pluralModelLabel = 'Syarat sewa';

    protected static ?string $navigationLabel = 'Syarat sewa';

    protected static ?string $slug = 'syarat-sewa';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RentalRequirementGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalRequirementGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalRequirementGroups::route('/'),
            'create' => CreateRentalRequirementGroup::route('/create'),
            'edit' => EditRentalRequirementGroup::route('/{record}/edit'),
        ];
    }
}
