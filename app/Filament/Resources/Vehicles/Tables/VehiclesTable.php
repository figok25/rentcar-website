<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'primaryImage', 'activeRates']))
            ->columns([
                ImageColumn::make('primaryImage.image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Kategori')->sortable(),
                SelectColumn::make('status')
                    ->label('Status unit')
                    ->options(VehicleStatus::options())
                    ->selectablePlaceholder(false),
                TextColumn::make('starting_price')
                    ->label('Mulai dari')
                    ->state(fn (Vehicle $record): ?int => $record->activeRates->min('price_per_day'))
                    ->money('IDR', locale: 'id')
                    ->placeholder('-'),
                ToggleColumn::make('is_published')->label('Tampil'),
                IconColumn::make('is_featured')->label('Unggulan')->boolean(),
                TextColumn::make('sort_order')->label('Urutan')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status unit')->options(VehicleStatus::options()),
                SelectFilter::make('vehicle_category_id')->label('Kategori')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_published')->label('Publikasi'),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
