<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Tulis hanya testimoni asli dari pelanggan.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_display_name')
                            ->label('Nama yang ditampilkan')
                            ->required()
                            ->maxLength(80)
                            ->helperText('Boleh inisial atau nama depan, mis. "Budi dari Surabaya".'),
                        TextInput::make('source_label')
                            ->label('Sumber (opsional)')
                            ->maxLength(80)
                            ->placeholder('Contoh: Google Maps, WhatsApp'),
                        Textarea::make('content')
                            ->label('Isi testimoni')
                            ->required()
                            ->rows(4)
                            ->maxLength(800)
                            ->columnSpanFull(),
                        Select::make('rating')
                            ->label('Bintang (opsional)')
                            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_published')
                            ->label('Tampilkan di website')
                            ->default(true),
                    ]),
            ]);
    }
}
