<?php

namespace Tests\Feature;

use App\Models\Advantage;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_location_and_official_map_embed(): void
    {
        Advantage::factory()->count(3)->create(['is_active' => true]);
        WebsiteSetting::set('address', 'Jl. Contoh No. 1, Malang');
        WebsiteSetting::set('maps_embed_url', 'https://www.google.com/maps/embed?pb=abc');

        $this->get('/')
            ->assertOk()
            ->assertSee('Kunjungi lokasi kami')
            ->assertSee('Jl. Contoh No. 1, Malang')
            ->assertSee('https://www.google.com/maps/embed?pb=abc', false);
    }

    public function test_home_never_embeds_a_non_google_map(): void
    {
        WebsiteSetting::set('address', 'Jl. Contoh No. 1');
        WebsiteSetting::set('maps_embed_url', 'https://evil.example/embed');

        $this->get('/')->assertOk()->assertDontSee('evil.example');
    }

    public function test_gallery_needs_four_photos_of_published_vehicles(): void
    {
        $category = VehicleCategory::factory()->create(['is_active' => true]);
        Vehicle::factory()->count(4)->create(['vehicle_category_id' => $category->id, 'is_published' => true])
            ->each(fn ($v) => VehicleImage::factory()->create(['vehicle_id' => $v->id]));

        $this->get('/')->assertOk()->assertSee('Galeri armada');

        Vehicle::query()->update(['is_published' => false]);
        $this->get('/')->assertOk()->assertDontSee('Galeri armada');
    }
}
