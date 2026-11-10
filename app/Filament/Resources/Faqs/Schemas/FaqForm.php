<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('answer')
                            ->label('Jawaban')
                            ->required()
                            ->rows(4)
                            ->maxLength(1500)
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
