<?php

namespace App\Enums;

/**
 * Dua katalog publik yang berbagi satu tabel `vehicles`:
 * /rental-mobil (semua kategori selain Motor) dan /rental-motor (kategori Motor).
 */
enum CatalogKind: string
{
    case Car = 'mobil';
    case Motor = 'motor';

    public function indexRoute(): string
    {
        return match ($this) {
            self::Car => 'vehicles.index',
            self::Motor => 'motor.index',
        };
    }

    public function showRoute(): string
    {
        return match ($this) {
            self::Car => 'vehicles.show',
            self::Motor => 'motor.show',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::Car => 'Rental Mobil',
            self::Motor => 'Rental Motor',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Car => 'Pilih mobil sesuai kebutuhan perjalanan Anda. Cek status unit, harga per hari, lalu tanyakan ketersediaan lewat WhatsApp.',
            self::Motor => 'Sewa motor harian untuk keliling kota. Cek status unit dan harga per hari, lalu tanyakan ketersediaan lewat WhatsApp.',
        };
    }
}
