<?php

namespace App\Filament\Resources\TravelServices\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TravelServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi layanan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama layanan')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        Textarea::make('short_description')
                            ->label('Ringkasan (untuk kartu)')
                            ->rows(2)
                            ->maxLength(300)
                            ->columnSpanFull(),
                        TextInput::make('route_label')
                            ->label('Rute')
                            ->maxLength(255)
                            ->placeholder('Contoh: Surabaya - Malang (pulang pergi)'),
                        TextInput::make('price_label')
                            ->label('Keterangan harga')
                            ->maxLength(255)
                            ->placeholder('Contoh: Mulai Rp 150.000 per orang')
                            ->helperText('Teks bebas. Kosongkan jika harga hanya via WhatsApp.'),
                    ]),

                Section::make('Isi layanan')
                    ->description('Untuk syarat dan ketentuan, tulis satu poin per baris.')
                    ->schema([
                        Textarea::make('description')->label('Deskripsi')->rows(5),
                        Textarea::make('vehicle_information')
                            ->label('Informasi armada')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Contoh: Hiace 14 seat, AC, bagasi luas'),
                        Textarea::make('terms')->label('Syarat dan ketentuan')->rows(5),
                    ]),

                Section::make('Tampil di website')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')->label('Dipublikasikan')->default(false),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->minValue(0)->default(0)->required(),
                    ]),

                Section::make('SEO (opsional)')
                    ->collapsed()
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')->label('Judul SEO')->maxLength(255),
                        Textarea::make('meta_description')->label('Deskripsi SEO')->rows(2)->maxLength(300),
                    ]),
            ]);
    }
}
