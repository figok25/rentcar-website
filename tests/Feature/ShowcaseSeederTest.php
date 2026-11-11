<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Tour;
use App\Models\TravelService;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Database\Seeders\ShowcaseSeeder;
use Database\Seeders\VehicleCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_complete_showcase_and_pages_render(): void
    {
        Storage::fake('public');

        $this->seed(ShowcaseSeeder::class);

        $this->assertSame(14, Vehicle::count());
        $this->assertSame(28, VehicleImage::count());
        $this->assertGreaterThanOrEqual(4, Tour::count());
        $this->assertGreaterThanOrEqual(4, TravelService::count());
        $this->assertGreaterThanOrEqual(5, Faq::count());

        foreach (['/', '/rental-mobil', '/rental-motor', '/paket-wisata', '/travel', '/syarat-sewa', '/kontak', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }

        $vehicle = Vehicle::query()->where('slug', 'toyota-avanza')->firstOrFail();
        $this->get('/rental-mobil/'.$vehicle->slug)->assertOk()->assertSee('Toyota Avanza')->assertSee('Rp 350.000');
        Storage::disk('public')->assertExists($vehicle->images()->first()->image_path);
    }

    public function test_is_idempotent(): void
    {
        Storage::fake('public');

        $this->seed(ShowcaseSeeder::class);
        $counts = [Vehicle::count(), VehicleImage::count(), Tour::count(), Faq::count()];
        $this->seed(ShowcaseSeeder::class);

        $this->assertSame($counts, [Vehicle::count(), VehicleImage::count(), Tour::count(), Faq::count()]);
    }

    public function test_hides_demo_vehicles_and_keeps_manual_ones(): void
    {
        Storage::fake('public');
        $this->seed(VehicleCategorySeeder::class);
        $manual = Vehicle::factory()->create(['name' => 'Unit Buatan Sendiri', 'is_published' => true]);
        $demo = Vehicle::factory()->create(['slug' => 'contoh-unit', 'is_published' => true]);

        $this->seed(ShowcaseSeeder::class);

        $this->assertTrue($manual->fresh()->is_published);
        $this->assertFalse($demo->fresh()->is_published);
    }

    public function test_refuses_to_run_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(\RuntimeException::class);

        // Dipanggil langsung: perintah db:seed di production meminta konfirmasi lebih dulu.
        (new ShowcaseSeeder)->run();
    }
}
