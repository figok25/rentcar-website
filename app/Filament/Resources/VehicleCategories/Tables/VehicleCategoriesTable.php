<?php

namespace App\Filament\Resources\VehicleCategories\Tables;

use App\Models\VehicleCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Kategori')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('vehicles_count')->label('Jumlah kendaraan')->counts('vehicles'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (VehicleCategory $record, DeleteAction $action): void {
                        if ($record->vehicles()->withTrashed()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Kategori tidak bisa dihapus')
                                ->body('Masih ada kendaraan di kategori ini. Pindahkan atau hapus kendaraannya dulu, atau nonaktifkan saja kategorinya.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->defaultSort('sort_order');
    }
}
