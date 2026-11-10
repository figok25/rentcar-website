<?php

namespace App\Services;

use App\Enums\RentalServiceType;
use App\Models\Vehicle;
use App\Models\WebsiteSetting;
use Carbon\CarbonImmutable;

/**
 * Menyusun teks pesan WhatsApp dari form rencana sewa.
 * Murni teks: tidak ada booking, harga final selalu dikonfirmasi admin.
 */
class WhatsAppMessageBuilder
{
    /** Batas durasi sewa yang masuk akal untuk satu pertanyaan (hari). */
    public const MAX_DAYS = 90;

    /**
     * Jumlah hari sewa; tanggal yang sama dihitung 1 hari.
     */
    public static function days(string $start, string $end): int
    {
        $diff = CarbonImmutable::parse($start)->startOfDay()->diffInDays(CarbonImmutable::parse($end)->startOfDay());

        return max(1, (int) $diff);
    }

    /**
     * @param  array{customer_name: string, start_date: string, end_date: string, service_type?: ?string, pickup_location?: ?string, notes?: ?string}  $data
     */
    public function build(Vehicle $vehicle, array $data): string
    {
        $business = WebsiteSetting::get('business_name', config('app.name'));
        $start = CarbonImmutable::parse($data['start_date'])->locale('id');
        $end = CarbonImmutable::parse($data['end_date'])->locale('id');
        $days = self::days($data['start_date'], $data['end_date']);

        $lines = [
            'Halo '.$business.', saya ingin menanyakan ketersediaan unit berikut.',
            '',
            'Unit: '.$vehicle->name,
        ];

        if (! empty($data['service_type'])) {
            $lines[] = 'Layanan: '.RentalServiceType::from($data['service_type'])->label();
        }

        $lines[] = 'Tanggal ambil: '.$start->translatedFormat('l, j F Y');
        $lines[] = 'Tanggal kembali: '.$end->translatedFormat('l, j F Y');
        $lines[] = 'Durasi: '.$days.' hari';

        if (! empty($data['pickup_location'])) {
            $lines[] = 'Lokasi pengambilan: '.$this->clean($data['pickup_location']);
        }

        $lines[] = 'Nama: '.$this->clean($data['customer_name']);

        if (! empty($data['notes'])) {
            $lines[] = 'Catatan: '.$this->clean($data['notes']);
        }

        $lines[] = '';
        $lines[] = 'Mohon info ketersediaan dan harga finalnya. Terima kasih.';

        return implode("\n", $lines);
    }

    /** Satu baris per isian agar format pesan tidak bisa dirusak. */
    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
