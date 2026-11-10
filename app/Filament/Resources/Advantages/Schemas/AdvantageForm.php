<?php

namespace App\Filament\Resources\Advantages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvantageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Penjelasan')
                            ->rows(3)
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
            ]);
    }
}
