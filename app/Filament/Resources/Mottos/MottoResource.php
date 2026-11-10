<?php

namespace App\Filament\Resources\Mottos;

use App\Filament\Resources\Mottos\Pages\CreateMotto;
use App\Filament\Resources\Mottos\Pages\EditMotto;
use App\Filament\Resources\Mottos\Pages\ListMottos;
use App\Filament\Resources\Mottos\Schemas\MottoForm;
use App\Filament\Resources\Mottos\Tables\MottosTable;
use App\Models\Motto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MottoResource extends Resource
{
    protected static ?string $model = Motto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Motto';

    protected static ?string $pluralModelLabel = 'Motto';

    protected static ?string $navigationLabel = 'Motto';

    protected static ?string $slug = 'motto';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return MottoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MottosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMottos::route('/'),
            'create' => CreateMotto::route('/create'),
            'edit' => EditMotto::route('/{record}/edit'),
        ];
    }
}
