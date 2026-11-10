<?php

namespace App\Filament\Resources\Tours\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ToursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('primaryImage'))
            ->columns([
                ImageColumn::make('primaryImage.image_path')->label('Foto')->disk('public')->square(),
                TextColumn::make('title')->label('Paket')->searchable()->sortable(),
                TextColumn::make('duration_label')->label('Durasi')->placeholder('-'),
                TextColumn::make('price')
                    ->label('Mulai dari')
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->placeholder('-')
                    ->sortable(),
                ToggleColumn::make('is_published')->label('Tampil'),
                IconColumn::make('is_featured')->label('Unggulan')->boolean(),
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
