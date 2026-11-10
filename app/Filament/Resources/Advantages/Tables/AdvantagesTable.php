<?php

namespace App\Filament\Resources\Advantages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvantagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('description')->label('Penjelasan')->limit(60)->toggleable(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
