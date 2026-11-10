<?php

namespace App\Filament\Resources\Testimonials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_display_name')->label('Nama')->searchable(),
                TextColumn::make('content')->label('Testimoni')->limit(60),
                TextColumn::make('rating')->label('Bintang')->toggleable(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                IconColumn::make('is_published')->label('Tampil')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
