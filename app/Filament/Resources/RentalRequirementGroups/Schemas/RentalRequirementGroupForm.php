<?php

namespace App\Filament\Resources\RentalRequirementGroups\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalRequirementGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelompok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama kelompok')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('Contoh: Syarat sewa lepas kunci'),
                        Select::make('vehicle_category_id')
                            ->label('Berlaku untuk kategori')
                            ->relationship('category', 'name')
                            ->placeholder('Semua kategori')
                            ->helperText('Kosongkan agar tampil di semua kendaraan. Jika dipilih, hanya tampil di unit kategori itu.'),
                        Textarea::make('description')
                            ->label('Keterangan (opsional)')
                            ->rows(2)
                            ->maxLength(300)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Tampilkan di website')
                            ->default(true),
                    ]),

                Section::make('Daftar syarat')
                    ->description('Tambahkan satu syarat per baris. Urutan bisa diseret. Kelompok tanpa syarat aktif tidak ditampilkan.')
                    ->schema([
                        Repeater::make('requirements')
                            ->label('Syarat')
                            ->relationship('requirements')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->addActionLabel('Tambah syarat')
                            ->defaultItems(0)
                            ->schema([
                                Textarea::make('requirement_text')
                                    ->label('Isi syarat')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
