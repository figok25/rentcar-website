<?php

namespace App\Enums;

/**
 * Kondisi unit yang dipilih manual oleh admin.
 * Bukan status publikasi dan tidak berubah otomatis dari form WhatsApp.
 */
enum VehicleStatus: string
{
    case Available = 'available';
    case Rented = 'rented';
    case Maintenance = 'maintenance';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Rented => 'Sedang disewa',
            self::Maintenance => 'Maintenance',
            self::Unavailable => 'Tidak tersedia',
        };
    }

    /**
     * Nama warna semantik untuk badge (dipetakan ke kelas CSS di layer tampilan).
     */
    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Rented => 'warning',
            self::Maintenance => 'info',
            self::Unavailable => 'danger',
        };
    }

    /**
     * @return array<string, string> value => label
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
