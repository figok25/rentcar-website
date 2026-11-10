<?php

namespace App\Filament\Resources\TravelServices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TravelServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Layanan')->searchable()->sortable(),
                TextColumn::make('route_label')->label('Rute')->placeholder('-')->searchable(),
                TextColumn::make('price_label')->label('Harga')->placeholder('-'),
                ToggleColumn::make('is_published')->label('Tampil'),
                TextColumn::make('sort_order')->label('Urutan')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publikasi'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
