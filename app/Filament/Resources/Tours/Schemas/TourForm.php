<?php

namespace App\Filament\Resources\Tours\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi paket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama paket')
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
                        TextInput::make('duration_label')
                            ->label('Durasi')
                            ->maxLength(80)
                            ->placeholder('Contoh: 2 hari 1 malam'),
                        TextInput::make('price')
                            ->label('Harga mulai dari')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->helperText('Per orang atau per paket sesuai kesepakatan. Kosongkan jika harga hanya via WhatsApp.'),
                    ]),

                Section::make('Isi paket')
                    ->description('Untuk daftar, tulis satu poin per baris.')
                    ->schema([
                        Textarea::make('description')->label('Deskripsi')->rows(5),
                        Textarea::make('itinerary')
                            ->label('Rencana perjalanan')
                            ->rows(6)
                            ->helperText('Satu baris per langkah, mis. "Hari 1: Penjemputan dan menuju kawah".'),
                        Textarea::make('included_facilities')->label('Sudah termasuk')->rows(4),
                        Textarea::make('excluded_facilities')->label('Belum termasuk')->rows(4),
                    ]),

                Section::make('Foto')
                    ->description('Tandai satu foto sebagai foto utama. Urutan foto bisa diseret.')
                    ->schema([
                        Repeater::make('images')
                            ->label('Foto paket')
                            ->relationship('images')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->addActionLabel('Tambah foto')
                            ->defaultItems(0)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('File foto')
                                    ->image()
                                    ->disk('public')
                                    ->directory('tours')
                                    ->visibility('public')
                                    ->maxSize(4096)
                                    ->required(),
                                TextInput::make('alt_text')->label('Teks alternatif (SEO)')->maxLength(255),
                                Toggle::make('is_primary')->label('Foto utama')->default(false),
                            ]),
                    ]),

                Section::make('Tampil di website')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_published')->label('Dipublikasikan')->default(false),
                        Toggle::make('is_featured')->label('Unggulan (beranda)')->default(false),
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
