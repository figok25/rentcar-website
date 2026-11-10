<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourImage;
use App\Models\TravelService;
use App\Models\WebsiteSetting;
use App\Support\Text;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ToursTravelTest extends TestCase
{
    use RefreshDatabase;

    private function enable(bool $tours = true, bool $travel = true): void
    {
        WebsiteSetting::set('feature_tours_enabled', $tours);
        WebsiteSetting::set('feature_travel_enabled', $travel);
        WebsiteSetting::set('whatsapp_number', '6281234567890');
    }

    public function test_text_lines_helper(): void
    {
        $this->assertSame(['A', 'B', 'C'], Text::lines("- A\n\n* B\r\n • C \n"));
        $this->assertSame([], Text::lines(null));
    }

    public function test_pages_are_404_while_features_disabled(): void
    {
        $tour = Tour::factory()->create();
        $service = TravelService::factory()->create();

        $this->get('/paket-wisata')->assertNotFound();
        $this->get('/paket-wisata/'.$tour->slug)->assertNotFound();
        $this->get('/travel')->assertNotFound();
        $this->get('/travel/'.$service->slug)->assertNotFound();
        $this->get('/')->assertDontSee('/paket-wisata', false)->assertDontSee('/travel', false);
    }

    public function test_each_feature_toggles_independently(): void
    {
        $this->enable(tours: true, travel: false);
        Tour::factory()->create();
        TravelService::factory()->create();

        $this->get('/paket-wisata')->assertOk();
        $this->get('/travel')->assertNotFound();
    }

    public function test_empty_lists_render_and_menu_hidden(): void
    {
        $this->enable();

        $this->get('/paket-wisata')->assertOk()->assertSee('belum tersedia');
        $this->get('/travel')->assertOk()->assertSee('belum tersedia');
        $this->get('/')->assertDontSee(route('tours.index'), false)->assertDontSee(route('travel.index'), false);
    }

    public function test_menu_appears_when_enabled_and_published_exists(): void
    {
        $this->enable();
        Tour::factory()->create();
        TravelService::factory()->create();

        $this->get('/kontak')->assertSee(route('tours.index'), false)->assertSee(route('travel.index'), false);
    }

    public function test_only_published_tours_listed_featured_first(): void
    {
        $this->enable();
        Tour::factory()->create(['title' => 'Biasa Satu', 'sort_order' => 1]);
        Tour::factory()->create(['title' => 'Unggulan Dua', 'sort_order' => 5, 'is_featured' => true]);
        Tour::factory()->unpublished()->create(['title' => 'Draf Rahasia']);

        $this->get('/paket-wisata')->assertOk()
            ->assertSeeInOrder(['Unggulan Dua', 'Biasa Satu'])
            ->assertDontSee('Draf Rahasia');
    }

    public function test_unpublished_or_unknown_detail_is_404(): void
    {
        $this->enable();
        $draft = Tour::factory()->unpublished()->create();
        $draftService = TravelService::factory()->create(['is_published' => false]);

        $this->get('/paket-wisata/'.$draft->slug)->assertNotFound();
        $this->get('/paket-wisata/tidak-ada')->assertNotFound();
        $this->get('/travel/'.$draftService->slug)->assertNotFound();
    }

    public function test_tour_detail_content(): void
    {
        $this->enable();
        $tour = Tour::factory()->create([
            'title' => 'Bromo Sunrise',
            'price' => 750000,
            'duration_label' => '1 hari',
            'itinerary' => "Penjemputan\nSunrise Penanjakan",
            'included_facilities' => "- Mobil\n- BBM",
            'excluded_facilities' => 'Tiket masuk',
        ]);

        $this->get('/paket-wisata/'.$tour->slug)->assertOk()
            ->assertSee('Rp 750.000')->assertSee('1 hari')
            ->assertSeeInOrder(['Penjemputan', 'Sunrise Penanjakan'])
            ->assertSee('Sudah termasuk')->assertSee('Mobil')
            ->assertSee('Belum termasuk')->assertSee('Tiket masuk');
    }

    public function test_tour_without_price_or_lists(): void
    {
        $this->enable();
        $tour = Tour::factory()->create(['price' => null, 'itinerary' => null, 'included_facilities' => null, 'excluded_facilities' => null]);

        $this->get('/paket-wisata/'.$tour->slug)->assertOk()
            ->assertSee('Tanyakan harga via WhatsApp')
            ->assertDontSee('Rencana perjalanan')->assertDontSee('Sudah termasuk');
    }

    public function test_content_is_escaped(): void
    {
        $this->enable();
        $tour = Tour::factory()->create(['description' => '<script>x()</script>', 'itinerary' => '<img src=x>']);

        $this->get('/paket-wisata/'.$tour->slug)->assertDontSee('<script>x()</script>', false)->assertDontSee('<img src=x>', false);
    }

    public function test_whatsapp_link_in_tour_detail(): void
    {
        $this->enable();
        $tour = Tour::factory()->create(['title' => 'Paket Ijen']);

        $this->get('/paket-wisata/'.$tour->slug)->assertSee('https://wa.me/6281234567890?text=', false)->assertSee('Paket%20Ijen', false);

        WebsiteSetting::set('whatsapp_number', 'abc');
        // Data situs dihitung sekali per request; di tes, request kedua memakai app yang sama.
        $this->app->forgetScopedInstances();
        $this->get('/paket-wisata/'.$tour->slug)->assertOk()->assertDontSee('wa.me', false);
    }

    public function test_home_shows_only_featured_published_tours_when_enabled(): void
    {
        Tour::factory()->create(['title' => 'Wisata Unggulan', 'is_featured' => true]);
        Tour::factory()->create(['title' => 'Wisata Biasa', 'is_featured' => false]);
        $this->get('/')->assertDontSee('Wisata Unggulan');

        $this->enable();
        $this->get('/')->assertSee('Wisata Unggulan')->assertDontSee('Wisata Biasa');
    }

    public function test_travel_pages(): void
    {
        $this->enable();
        $a = TravelService::factory()->create(['title' => 'Travel A', 'route_label' => 'Sby - Mlg', 'price_label' => 'Mulai Rp 120.000', 'vehicle_information' => 'Hiace', 'terms' => "Datang awal\nBatal H-1"]);
        $b = TravelService::factory()->create(['title' => 'Travel B']);

        $this->get('/travel')->assertSee('Travel A')->assertSee('Travel B');
        $this->get('/travel/'.$a->slug)->assertOk()
            ->assertSee('Sby - Mlg')->assertSee('Mulai Rp 120.000')->assertSee('Hiace')
            ->assertSeeInOrder(['Datang awal', 'Batal H-1'])
            ->assertSee('Travel B');
    }

    public function test_deleting_tour_removes_image_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tours/a.jpg', 'x');
        Storage::disk('public')->put('tours/b.jpg', 'x');
        $tour = Tour::factory()->create();
        TourImage::factory()->create(['tour_id' => $tour->id, 'image_path' => 'tours/a.jpg']);
        TourImage::factory()->create(['tour_id' => $tour->id, 'image_path' => 'tours/b.jpg']);

        $tour->delete();

        Storage::disk('public')->assertMissing('tours/a.jpg');
        Storage::disk('public')->assertMissing('tours/b.jpg');
        $this->assertDatabaseCount('tour_images', 0);
    }

    public function test_normalize_primary_image(): void
    {
        $tour = Tour::factory()->create();
        $first = TourImage::factory()->create(['tour_id' => $tour->id, 'is_primary' => false, 'sort_order' => 1]);
        $second = TourImage::factory()->create(['tour_id' => $tour->id, 'is_primary' => true, 'sort_order' => 2]);
        $third = TourImage::factory()->create(['tour_id' => $tour->id, 'is_primary' => true, 'sort_order' => 3]);

        $tour->normalizePrimaryImage();

        $this->assertSame(1, $tour->images()->where('is_primary', true)->count());
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertFalse($first->fresh()->is_primary);
    }
}
