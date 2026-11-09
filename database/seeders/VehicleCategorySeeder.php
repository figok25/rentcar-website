<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleCategorySeeder extends Seeder
{
    /**
     * Kategori awal. Admin dapat mengubah, menambah, mengurutkan, dan menonaktifkan dari CMS.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Mobil Kota', 'slug' => 'mobil-kota'],
            ['name' => 'Mobil Premium', 'slug' => 'mobil-premium'],
            ['name' => 'Minibus / Hiace', 'slug' => 'minibus-hiace'],
            ['name' => 'Motor', 'slug' => VehicleCategory::MOTOR_SLUG],
        ];

        foreach ($categories as $index => $category) {
            VehicleCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }
}
