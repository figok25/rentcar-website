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
use App\Models\RentalRequirementGroup;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\TravelService;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\WebsiteSetting;
use Database\Seeders\Support\PlaceholderArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data contoh LENGKAP untuk tangkapan layar / showcase.
 *
 *   php artisan db:seed --class=ShowcaseSeeder
 *
 * Aman dijalankan berulang (memperbarui data berdasarkan slug/judul). Foto berupa
 * ilustrasi SVG berlabel "Foto contoh"; ganti dengan foto asli dari admin.
 * Tidak boleh dijalankan di production.
 */
class ShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('ShowcaseSeeder berisi data contoh dan tidak boleh dijalankan di production.');
        }

        $this->call([VehicleCategorySeeder::class, WebsiteSettingSeeder::class]);

        $this->settings();
        $this->vehicles();
        $this->fees();
        $this->requirements();
        $this->content();
        $this->tours();
        $this->travel();

        cache()->forget('website_settings.all');
    }

    private function settings(): void
    {
        $values = [
            ['business_name', 'Rental Kendaraan Nusantara', 'general'],
            ['phone_number', '031 0000 0000', 'contact'],
            ['address', "Jl. Contoh Raya No. 12\nSurabaya, Jawa Timur", 'contact'],
            ['hero_title', 'Sewa Kendaraan Mudah dan Terpercaya', 'home'],
            ['footer_description', 'Rental mobil, motor, paket wisata, dan travel. Tanyakan ketersediaan langsung lewat WhatsApp.', 'home'],
            ['feature_motor_enabled', '1', 'features'],
            ['feature_tours_enabled', '1', 'features'],
            ['feature_travel_enabled', '1', 'features'],
        ];

        foreach ($values as [$key, $value, $group]) {
            WebsiteSetting::set($key, $value, $group);
        }
    }

    private function vehicles(): void
    {
        // Unit "Contoh ..." dari DemoDataSeeder disembunyikan agar tidak bercampur.
        Vehicle::query()->where('slug', 'like', 'contoh-%')->update(['is_published' => false]);

        $categories = VehicleCategory::query()->pluck('id', 'slug');
        $A = TransmissionType::Automatic;
        $M = TransmissionType::Manual;
        $G = FuelType::Gasoline;
        $D = FuelType::Diesel;
        $S = RentalServiceType::SelfDrive;
        $W = RentalServiceType::WithDriver;

        // [kategori, nama, merek, model, tahun, transmisi, bbm, kursi, status, unggulan, bentuk, warna, tarif[]]
        $rows = [
            ['mobil-kota', 'Toyota Agya', 'Toyota', 'Agya', 2022, $A, $G, 5, VehicleStatus::Available, true, 'hatch', '#c9372d', [[$S, 280000]]],
            ['mobil-kota', 'Honda Brio RS', 'Honda', 'Brio', 2022, $A, $G, 5, VehicleStatus::Available, false, 'hatch', '#2f6db5', [[$S, 300000]]],
            ['mobil-kota', 'Daihatsu Sigra', 'Daihatsu', 'Sigra', 2021, $M, $G, 7, VehicleStatus::Available, false, 'mpv', '#9aa5ad', [[$S, 300000], [$W, 520000]]],
            ['mobil-kota', 'Toyota Avanza', 'Toyota', 'Avanza', 2023, $M, $G, 7, VehicleStatus::Rented, true, 'mpv', '#f2f2f2', [[$S, 350000], [$W, 550000]]],
            ['mobil-kota', 'Mitsubishi Xpander', 'Mitsubishi', 'Xpander', 2023, $A, $G, 7, VehicleStatus::Available, true, 'mpv', '#6b7480', [[$S, 450000], [$W, 650000]]],
            ['mobil-kota', 'Suzuki Ertiga', 'Suzuki', 'Ertiga', 2021, $A, $G, 7, VehicleStatus::Maintenance, false, 'mpv', '#b9b9b9', [[$S, 400000], [$W, 600000]]],
            ['mobil-premium', 'Toyota Innova Reborn', 'Toyota', 'Innova', 2022, $M, $D, 7, VehicleStatus::Available, true, 'mpv', '#3a3f45', [[$S, 600000], [$W, 800000]]],
            ['mobil-premium', 'Toyota Fortuner', 'Toyota', 'Fortuner', 2022, $A, $D, 7, VehicleStatus::Available, false, 'suv', '#2b3a4a', [[$S, 1000000], [$W, 1300000]]],
            ['mobil-premium', 'Toyota Alphard', 'Toyota', 'Alphard', 2021, $A, $G, 7, VehicleStatus::Available, true, 'van', '#e9e9e9', [[$W, 2200000]]],
            ['minibus-hiace', 'Toyota Hiace Commuter', 'Toyota', 'Hiace', 2022, $M, $D, 15, VehicleStatus::Available, true, 'minibus', '#f5f5f5', [[$W, 1200000]]],
            ['minibus-hiace', 'Isuzu Elf Long', 'Isuzu', 'Elf', 2020, $M, $D, 19, VehicleStatus::Unavailable, false, 'minibus', '#dcdcdc', [[$W, 1500000]]],
            ['motor', 'Honda Vario 160', 'Honda', 'Vario', 2023, $A, $G, 2, VehicleStatus::Available, false, 'motor', '#1d4e89', [[$S, 100000]]],
            ['motor', 'Yamaha NMAX', 'Yamaha', 'NMAX', 2022, $A, $G, 2, VehicleStatus::Rented, false, 'motor', '#2b333b', [[$S, 150000]]],
            ['motor', 'Honda Beat', 'Honda', 'Beat', 2023, $A, $G, 2, VehicleStatus::Available, false, 'motor', '#c9372d', [[$S, 80000]]],
        ];

        foreach ($rows as $i => [$cat, $name, $brand, $model, $year, $trans, $fuel, $seats, $status, $featured, $shape, $color, $rates]) {
            $slug = str($name)->slug()->toString();

            $vehicle = Vehicle::query()->withTrashed()->updateOrCreate(['slug' => $slug], [
                'vehicle_category_id' => $categories[$cat],
                'name' => $name,
                'brand' => $brand,
                'model' => $model,
                'year' => $year,
                'transmission' => $trans,
                'fuel_type' => $fuel,
                'passenger_capacity' => $seats,
                'description' => $this->vehicleDescription($name, $seats, $shape),
                'status' => $status,
                'is_featured' => $featured,
                'is_published' => true,
                'sort_order' => ($i + 1) * 10,
            ]);
            $vehicle->restore();

            foreach ($rates as [$service, $price]) {
                $vehicle->rates()->updateOrCreate(['service_type' => $service], [
                    'price_per_day' => $price,
                    'includes_driver' => $service === RentalServiceType::WithDriver,
                    'includes_fuel' => false,
                    'notes' => $service === RentalServiceType::WithDriver ? 'Sudah termasuk driver. BBM, tol, dan parkir ditanggung penyewa.' : null,
                    'is_active' => true,
                ]);
            }

            $subtitle = $vehicle->category()->value('name');
            foreach ([0, 1] as $n) {
                $path = "vehicles/showcase/{$slug}-".($n + 1).'.svg';
                Storage::disk('public')->put($path, PlaceholderArt::vehicle($shape, $color, $name, $subtitle, $n === 1, $i + $n));

                $vehicle->images()->updateOrCreate(['image_path' => $path], [
                    'alt_text' => $name.($n === 0 ? ' tampak samping' : ' tampak sisi lain'),
                    'is_primary' => $n === 0,
                    'sort_order' => $n + 1,
                ]);
            }
        }
    }

    private function vehicleDescription(string $name, int $seats, string $shape): string
    {
        $kind = match ($shape) {
            'motor' => 'Motor matic yang lincah untuk berkeliling kota.',
            'minibus' => 'Cocok untuk rombongan keluarga, kantor, atau acara.',
            'van' => 'Nyaman untuk tamu penting dan perjalanan keluarga besar.',
            'suv' => 'Tangguh untuk perjalanan jauh maupun medan menanjak.',
            'mpv' => 'Lega untuk keluarga dan perjalanan luar kota.',
            default => 'Irit dan mudah dikendarai untuk dalam kota.',
        };

        return "{$name}: {$kind}\nKapasitas {$seats} orang. Unit dirawat berkala dan diperiksa sebelum disewakan.\nData ini hanya contoh untuk tampilan.";
    }

    private function fees(): void
    {
        $fees = [
            ['Biaya antar dan ambil kendaraan', 50000, 'Dalam kota, sesuai lokasi.'],
            ['Overtime per jam', 50000, 'Keterlambatan pengembalian.'],
            ['Biaya keliling kota', 100000, 'Pemakaian berkeliling di dalam kota.'],
            ['Biaya luar kota', 150000, 'Perjalanan di luar area kota.'],
            ['Kursi bayi (child seat)', 30000, 'Per hari, tergantung stok.'],
        ];

        foreach ($fees as $i => [$name, $amount, $desc]) {
            AdditionalFee::query()->updateOrCreate(['name' => $name], [
                'amount' => $amount, 'description' => $desc, 'sort_order' => ($i + 1) * 10, 'is_active' => true,
            ]);
        }
    }

    private function requirements(): void
    {
        $motor = VehicleCategory::query()->where('slug', VehicleCategory::MOTOR_SLUG)->value('id');

        $groups = [
            ['Syarat umum', null, 'Berlaku untuk semua unit.', [
                'KTP asli penyewa',
                'SIM A (mobil) atau SIM C (motor) yang masih berlaku',
                'Foto atau fotokopi identitas kedua (KK atau paspor)',
                'Deposit sesuai kesepakatan, dikembalikan saat unit kembali dalam kondisi baik',
            ]],
            ['Sewa dengan driver', null, 'Untuk layanan beserta pengemudi.', [
                'Jadwal dan rute perjalanan dikonfirmasi sebelum berangkat',
                'BBM, tol, parkir, dan makan driver ditanggung penyewa',
                'Pemakaian di atas 12 jam dikenakan biaya overtime',
            ]],
            ['Khusus sewa motor', $motor, 'Berlaku untuk kategori motor.', [
                'Wajib memakai helm yang disediakan',
                'Tidak diperkenankan dipinjamkan kepada orang lain',
            ]],
        ];

        foreach ($groups as $i => [$name, $category, $desc, $items]) {
            $group = RentalRequirementGroup::query()->updateOrCreate(['name' => $name], [
                'vehicle_category_id' => $category, 'description' => $desc, 'sort_order' => ($i + 1) * 10, 'is_active' => true,
            ]);

            foreach ($items as $n => $text) {
                $group->requirements()->updateOrCreate(['requirement_text' => $text], ['sort_order' => $n + 1, 'is_active' => true]);
            }
        }
    }

    private function content(): void
    {
        foreach ([['Unit terawat', 'Diperiksa berkala sebelum disewakan.'], ['Harga transparan', 'Tarif dan biaya tambahan dijelaskan di awal.'], ['Respons cepat', 'Tanya langsung lewat WhatsApp.']] as $i => [$t, $d]) {
            Motto::query()->updateOrCreate(['title' => $t], ['description' => $d, 'sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }

        $advantages = [
            ['Armada beragam', 'Dari mobil kota, premium, minibus, hingga motor.'],
            ['Lepas kunci atau dengan driver', 'Pilih layanan sesuai kebutuhan perjalanan Anda.'],
            ['Konsultasi langsung via WhatsApp', 'Tanya ketersediaan dan harga tanpa registrasi.'],
            ['Syarat jelas', 'Persyaratan sewa dapat dibaca sebelum menghubungi kami.'],
            ['Unit bersih dan terawat', 'Dicek kondisi dan kebersihannya sebelum serah terima.'],
            ['Paket wisata dan travel', 'Satu tempat untuk sewa kendaraan dan perjalanan.'],
        ];

        foreach ($advantages as $i => [$t, $d]) {
            Advantage::query()->updateOrCreate(['title' => $t], ['description' => $d, 'sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }

        $faqs = [
            ['Apakah form tanggal sewa langsung memesan unit?', 'Tidak. Form hanya menyiapkan pesan WhatsApp. Ketersediaan dan harga akhir dikonfirmasi oleh admin.'],
            ['Apa bedanya lepas kunci dan dengan driver?', 'Lepas kunci: Anda mengemudi sendiri. Dengan driver: unit disewa beserta pengemudi, BBM dan biaya perjalanan mengikuti kesepakatan.'],
            ['Dokumen apa yang harus disiapkan?', 'KTP asli dan SIM yang masih berlaku. Daftar lengkapnya ada di halaman Syarat Sewa.'],
            ['Apakah bisa sewa untuk luar kota?', 'Bisa. Sampaikan tujuan dan lama perjalanan lewat WhatsApp agar kami hitung biaya tambahannya.'],
            ['Bagaimana jika unit yang saya inginkan sedang disewa?', 'Status unit tertera di setiap kartu. Hubungi kami untuk jadwal tersedia atau unit pengganti yang sejenis.'],
            ['Apakah ada biaya tambahan?', 'Ada beberapa, misalnya antar-jemput, overtime, dan luar kota. Rinciannya tampil di halaman detail unit.'],
            ['Bagaimana cara pembayarannya?', 'Pembayaran dan deposit disepakati langsung dengan admin setelah unit dikonfirmasi. Website ini tidak memproses pembayaran.'],
        ];

        foreach ($faqs as $i => [$q, $a]) {
            Faq::query()->updateOrCreate(['question' => $q], ['answer' => $a, 'sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }

        $testimonials = [
            ['Contoh Pelanggan A', 'Proses tanya lewat WhatsApp cepat dan jelas. Unit bersih saat diterima.', 5],
            ['Contoh Pelanggan B', 'Harga sesuai yang disebutkan di awal, tidak ada biaya mendadak.', 5],
            ['Contoh Pelanggan C', 'Sewa Hiace untuk acara keluarga, drivernya ramah dan tepat waktu.', 4],
            ['Contoh Pelanggan D', 'Motornya terawat dan helmnya disediakan. Cocok untuk keliling kota.', 5],
            ['Contoh Pelanggan E', 'Antar jemput bandara tepat waktu, drivernya sopan.', 5],
            ['Contoh Pelanggan F', 'Paket Bromo seru dan jadwalnya rapi. Akan sewa lagi.', 4],
        ];

        foreach ($testimonials as $i => [$name, $text, $rating]) {
            Testimonial::query()->updateOrCreate(['customer_display_name' => $name], [
                'content' => $text, 'rating' => $rating, 'source_label' => 'Contoh testimoni',
                'is_published' => true, 'sort_order' => ($i + 1) * 10,
            ]);
        }
    }

    private function tours(): void
    {
        // [judul, ringkasan, durasi, harga, unggulan, variasi gambar, itinerary, termasuk, tidak termasuk]
        $tours = [
            ['Bromo Sunrise', 'Menikmati matahari terbit dari Penanjakan dan lautan pasir Bromo.', '1 hari', 750000, true, 0,
                "Dini hari: penjemputan dan perjalanan menuju Bromo\nSunrise di Penanjakan\nLautan pasir dan kawah Bromo\nSarapan, lalu kembali ke kota",
                "Mobil dan driver\nBBM dan parkir\nJeep di kawasan Bromo", "Tiket masuk kawasan\nMakan di luar sarapan\nPengeluaran pribadi"],
            ['Malang Batu 2 Hari 1 Malam', 'Wisata kota Malang dan Batu bersama keluarga.', '2 hari 1 malam', 1450000, true, 1,
                "Hari 1: Jatim Park dan alun-alun Batu\nHari 1: Makan malam dan check-in hotel\nHari 2: Museum dan pusat oleh-oleh\nHari 2: Perjalanan pulang",
                "Mobil dan driver\nBBM, tol, dan parkir\nHotel 1 malam", "Tiket wisata\nMakan siang dan malam"],
            ['Ijen Blue Fire', 'Menyaksikan api biru Kawah Ijen saat dini hari.', '2 hari 1 malam', 1850000, true, 2,
                "Hari 1: Perjalanan ke Banyuwangi dan istirahat\nHari 1: Berangkat tengah malam menuju Paltuding\nHari 2: Pendakian, api biru, dan sunrise\nHari 2: Kembali ke kota",
                "Mobil dan driver\nPenginapan 1 malam\nMasker gas", "Tiket masuk\nPemandu pendakian"],
            ['City Tour Surabaya', 'Berkeliling ikon kota Surabaya setengah hari.', 'Setengah hari', 450000, false, 3,
                "House of Sampoerna\nTugu Pahlawan dan Museum Sepuluh Nopember\nJembatan Suroboyo\nWisata kuliner khas Surabaya",
                "Mobil dan driver\nBBM dan parkir", "Tiket masuk\nMakan"],
            ['Paket Wisata Keluarga (Sesuai Permintaan)', 'Rute dan jadwal disusun sesuai kebutuhan rombongan Anda.', 'Fleksibel', null, false, 1,
                'Rute disusun bersama setelah Anda menghubungi kami', 'Kendaraan dan driver', 'Disesuaikan dengan paket'],
        ];

        foreach ($tours as $i => [$title, $short, $duration, $price, $featured, $variant, $itinerary, $in, $out]) {
            $slug = str($title)->slug()->toString();
            $tour = Tour::query()->updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'short_description' => $short,
                'description' => $short."\nData ini hanya contoh untuk tampilan; jadwal dan harga final dikonfirmasi admin.",
                'price' => $price,
                'duration_label' => $duration,
                'itinerary' => $itinerary,
                'included_facilities' => $in,
                'excluded_facilities' => $out,
                'is_published' => true,
                'is_featured' => $featured,
                'sort_order' => ($i + 1) * 10,
            ]);

            foreach ([0, 1] as $n) {
                $path = "tours/showcase/{$slug}-".($n + 1).'.svg';
                Storage::disk('public')->put($path, PlaceholderArt::landscape($title, $duration, $variant + $n, $n === 1));
                $tour->images()->updateOrCreate(['image_path' => $path], [
                    'alt_text' => $title, 'is_primary' => $n === 0, 'sort_order' => $n + 1,
                ]);
            }
        }
    }

    private function travel(): void
    {
        $services = [
            ['Travel Surabaya - Malang', 'Surabaya - Malang (pulang pergi)', 'Mulai Rp 120.000 per orang', 'Hiace 14 seat, AC, bagasi luas', 'Berangkat harian dari Surabaya menuju Malang dan sebaliknya.'],
            ['Travel Surabaya - Banyuwangi', 'Surabaya - Banyuwangi', 'Mulai Rp 250.000 per orang', 'Hiace 14 seat, kursi reclining', 'Perjalanan malam yang nyaman menuju Banyuwangi.'],
            ['Antar Jemput Bandara Juanda', 'Surabaya - Bandara Juanda', 'Mulai Rp 150.000 per trip', 'Mobil kota 4-7 seat', 'Antar jemput tepat waktu sesuai jadwal penerbangan Anda.'],
            ['Carter Hiace Luar Kota', 'Sesuai tujuan', null, 'Hiace Commuter 15 seat dengan driver', 'Sewa satu unit untuk rombongan ke tujuan pilihan Anda.'],
        ];

        foreach ($services as $i => [$title, $route, $price, $fleet, $short]) {
            TravelService::query()->updateOrCreate(['slug' => str($title)->slug()->toString()], [
                'title' => $title,
                'short_description' => $short,
                'description' => $short."\nJadwal keberangkatan dan ketersediaan kursi dikonfirmasi admin lewat WhatsApp.\nData ini hanya contoh untuk tampilan.",
                'route_label' => $route,
                'price_label' => $price,
                'vehicle_information' => $fleet,
                'terms' => "Datang 15 menit sebelum keberangkatan\nPembatalan maksimal H-1\nBagasi besar dikonfirmasi sebelumnya",
                'is_published' => true,
                'sort_order' => ($i + 1) * 10,
            ]);
        }
    }
}
