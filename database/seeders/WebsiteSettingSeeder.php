<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingSeeder extends Seeder
{
    /**
     * Nilai awal bersifat sementara. Semua dapat diubah admin dari halaman Pengaturan Website.
     * Pengaturan yang sudah ada tidak ditimpa saat seeder dijalankan ulang.
     */
    public function run(): void
    {
        $settings = [
            // Umum
            ['key' => 'business_name', 'value' => 'Rental Kendaraan', 'group' => 'general'],
            ['key' => 'logo_path', 'value' => null, 'group' => 'general'],

            // Kontak. Nomor WhatsApp: format internasional, angka saja.
            ['key' => 'whatsapp_number', 'value' => '6281200000000', 'group' => 'contact'],
            ['key' => 'phone_number', 'value' => null, 'group' => 'contact'],
            ['key' => 'address', 'value' => null, 'group' => 'contact'],
            ['key' => 'maps_embed_url', 'value' => null, 'group' => 'contact'],

            // Sosial media
            ['key' => 'instagram_url', 'value' => null, 'group' => 'social'],
            ['key' => 'facebook_url', 'value' => null, 'group' => 'social'],

            // Beranda
            ['key' => 'hero_title', 'value' => 'Sewa Kendaraan Mudah dan Terpercaya', 'group' => 'home'],
            ['key' => 'hero_subtitle', 'value' => 'Pilih unit, atur rencana tanggal sewa, lalu konsultasikan langsung dengan kami melalui WhatsApp.', 'group' => 'home'],
            ['key' => 'hero_image_path', 'value' => null, 'group' => 'home'],
            ['key' => 'footer_description', 'value' => null, 'group' => 'home'],

            // Fitur opsional (1 = aktif, 0 = nonaktif)
            ['key' => 'feature_motor_enabled', 'value' => '0', 'group' => 'features'],
            ['key' => 'feature_tours_enabled', 'value' => '0', 'group' => 'features'],
            ['key' => 'feature_travel_enabled', 'value' => '0', 'group' => 'features'],
        ];

        foreach ($settings as $setting) {
            WebsiteSetting::query()->firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']],
            );
        }

        // DatabaseSeeder memakai WithoutModelEvents, jadi cache dibersihkan manual.
        WebsiteSetting::flushCache();
    }
}
