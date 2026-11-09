<?php

namespace App\Filament\Resources\AdditionalFees\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdditionalFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama biaya')
                            ->placeholder('Contoh: Overtime driver per jam')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->helperText('Isi 0 jika biaya ditentukan saat konfirmasi.'),
                        Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
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
            ]);
    }
}
