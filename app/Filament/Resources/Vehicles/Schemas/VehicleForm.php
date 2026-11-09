<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Enums\FuelType;
use App\Enums\RentalServiceType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi kendaraan')
                    ->columns(2)
                    ->schema([
                        Select::make('vehicle_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label('Nama kendaraan')
                            ->placeholder('Contoh: Toyota Avanza 2022')
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
                        TextInput::make('brand')->label('Merek')->maxLength(255),
                        TextInput::make('model')->label('Model')->maxLength(255),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(1980)
                            ->maxValue((int) date('Y') + 1),
                        Select::make('transmission')
                            ->label('Transmisi')
                            ->options(TransmissionType::options())
                            ->default(TransmissionType::Manual->value)
                            ->required()
                            ->native(false),
                        Select::make('fuel_type')
                            ->label('Bahan bakar')
                            ->options(FuelType::options())
                            ->default(FuelType::Gasoline->value)
                            ->required()
                            ->native(false),
                        TextInput::make('passenger_capacity')
                            ->label('Kapasitas penumpang')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->default(1)
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & publikasi')
                    ->description('Status unit dipilih manual. Kendaraan hanya tampil di website jika "Tampilkan di website" aktif.')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status unit')
                            ->options(VehicleStatus::options())
                            ->default(VehicleStatus::Available->value)
                            ->required()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Urutan tampil')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_published')
                            ->label('Tampilkan di website')
                            ->default(false),
                        Toggle::make('is_featured')
                            ->label('Unggulan (beranda)')
                            ->default(false),
                    ]),

                Section::make('Foto')
                    ->description('Tandai satu foto sebagai foto utama. Urutan foto bisa diseret.')
                    ->schema([
                        Repeater::make('images')
                            ->label('Foto kendaraan')
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
                                    ->directory('vehicles')
                                    ->visibility('public')
                                    ->maxSize(4096)
                                    ->required(),
                                TextInput::make('alt_text')
                                    ->label('Teks alternatif (SEO)')
                                    ->maxLength(255),
                                Toggle::make('is_primary')
                                    ->label('Foto utama')
                                    ->default(false),
                            ]),
                    ]),

                Section::make('Tarif sewa')
                    ->description('Isi tarif per hari untuk tiap jenis layanan. Kosongkan jika harga hanya via WhatsApp.')
                    ->schema([
                        Repeater::make('rates')
                            ->label('Tarif')
                            ->relationship('rates')
                            ->addActionLabel('Tambah tarif')
                            ->maxItems(count(RentalServiceType::cases()))
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                Select::make('service_type')
                                    ->label('Jenis layanan')
                                    ->options(RentalServiceType::options())
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->native(false),
                                TextInput::make('price_per_day')
                                    ->label('Harga per hari')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required(),
                                Toggle::make('includes_driver')->label('Termasuk driver'),
                                Toggle::make('includes_fuel')->label('Termasuk BBM'),
                                Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
                                Toggle::make('is_active')->label('Tarif aktif')->default(true),
                            ]),
                    ]),

                Section::make('SEO')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta title')
                            ->maxLength(70),
                        Textarea::make('meta_description')
                            ->label('Meta description')
                            ->rows(2)
                            ->maxLength(160),
                    ]),
            ]);
    }
}
