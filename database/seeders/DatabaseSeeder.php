<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            VehicleCategorySeeder::class,
            WebsiteSettingSeeder::class,
        ]);

        // Data contoh hanya untuk lingkungan lokal.
        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
