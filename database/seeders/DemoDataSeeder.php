<?php

namespace Database\Seeders;

use App\Enums\FuelType;
use App\Enums\RentalServiceType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Models\AdditionalFee;
use App\Models\Advantage;
use App\Models\Faq;
use App\Models\Motto;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk pengembangan lokal. Seluruh isi (nama unit, harga, biaya, teks)
 * hanya dummy dan harus diganti dengan data asli dari CMS. Tidak dijalankan di production.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = VehicleCategory::query()->pluck('id', 'slug');

        $vehicles = [
            [
                'category' => 'mobil-kota', 'name' => 'Contoh Hatchback Matic', 'slug' => 'contoh-hatchback-matic',
                'brand' => 'Contoh', 'transmission' => TransmissionType::Automatic, 'capacity' => 5,
                'status' => VehicleStatus::Available, 'featured' => true,
                'rates' => [[RentalServiceType::SelfDrive, 300000, false, false]],
            ],
            [
                'category' => 'mobil-kota', 'name' => 'Contoh MPV Manual', 'slug' => 'contoh-mpv-manual',
                'brand' => 'Contoh', 'transmission' => TransmissionType::Manual, 'capacity' => 7,
                'status' => VehicleStatus::Rented, 'featured' => true,
                'rates' => [
                    [RentalServiceType::SelfDrive, 350000, false, false],
                    [RentalServiceType::WithDriver, 550000, true, false],
                ],
            ],
            [
                'category' => 'minibus-hiace', 'name' => 'Contoh Minibus 15 Seat', 'slug' => 'contoh-minibus-15-seat',
                'brand' => 'Contoh', 'transmission' => TransmissionType::Manual, 'capacity' => 15,
                'status' => VehicleStatus::Maintenance, 'featured' => false,
                'fuel' => FuelType::Diesel,
                'rates' => [[RentalServiceType::WithDriver, 1200000, true, false]],
            ],
            [
                'category' => 'motor', 'name' => 'Contoh Motor Matic', 'slug' => 'contoh-motor-matic',
                'brand' => 'Contoh', 'transmission' => TransmissionType::Automatic, 'capacity' => 2,
                'status' => VehicleStatus::Available, 'featured' => false,
                'rates' => [[RentalServiceType::SelfDrive, 100000, false, false]],
            ],
        ];

        foreach ($vehicles as $index => $data) {
            $vehicle = Vehicle::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'vehicle_category_id' => $categories[$data['category']],
                    'name' => $data['name'],
                    'brand' => $data['brand'],
                    'transmission' => $data['transmission'],
                    'fuel_type' => $data['fuel'] ?? FuelType::Gasoline,
                    'passenger_capacity' => $data['capacity'],
                    'description' => 'Data contoh. Ganti dengan deskripsi unit yang sebenarnya dari CMS.',
                    'status' => $data['status'],
                    'is_featured' => $data['featured'],
                    'is_published' => true,
                    'sort_order' => ($index + 1) * 10,
                ],
            );

            foreach ($data['rates'] as [$service, $price, $driver, $fuel]) {
                $vehicle->rates()->firstOrCreate(
                    ['service_type' => $service],
                    [
                        'price_per_day' => $price,
                        'includes_driver' => $driver,
                        'includes_fuel' => $fuel,
                        'is_active' => true,
                    ],
                );
            }
        }

        $fees = [
            ['name' => 'Biaya antar dan ambil kendaraan', 'amount' => 50000],
            ['name' => 'Biaya keliling kota', 'amount' => 100000],
        ];

        foreach ($fees as $index => $fee) {
            AdditionalFee::query()->firstOrCreate(
                ['name' => $fee['name']],
                ['amount' => $fee['amount'], 'description' => 'Nominal contoh.', 'sort_order' => ($index + 1) * 10, 'is_active' => true],
            );
        }

        $mottos = ['Unit terawat', 'Harga transparan', 'Respons cepat'];

        foreach ($mottos as $index => $title) {
            Motto::query()->firstOrCreate(
                ['title' => $title],
                ['description' => 'Teks contoh.', 'sort_order' => ($index + 1) * 10, 'is_active' => true],
            );
        }

        Advantage::query()->firstOrCreate(
            ['title' => 'Konsultasi langsung via WhatsApp'],
            ['description' => 'Teks contoh.', 'sort_order' => 10, 'is_active' => true],
        );

        Faq::query()->firstOrCreate(
            ['question' => 'Apakah form tanggal sewa langsung memesan unit?'],
            [
                'answer' => 'Tidak. Form hanya menyiapkan pesan WhatsApp. Ketersediaan dan harga akhir dikonfirmasi oleh admin.',
                'sort_order' => 10,
                'is_active' => true,
            ],
        );
    }
}
