<?php

namespace App\Filament\Resources\Mottos\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MottoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Beranda menampilkan maksimal 3 motto aktif pertama.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(80)
                            ->placeholder('Contoh: Unit terawat'),
                        Textarea::make('description')
                            ->label('Keterangan singkat')
                            ->rows(2)
                            ->maxLength(200)
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
