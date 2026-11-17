<?php

namespace Tests\Feature;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleRate;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCardTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attrs = [], ?int $price = 300000): Vehicle
    {
        $cat = VehicleCategory::firstOrCreate(['slug' => 'mobil-kota'], ['name' => 'Mobil Kota', 'is_active' => true]);
        $v = Vehicle::factory()->create(['vehicle_category_id' => $cat->id] + $attrs);
        if ($price !== null) {
            VehicleRate::factory()->create(['vehicle_id' => $v->id, 'price_per_day' => $price]);
        }

        return $v;
    }

    public function test_featured_vehicle_gets_the_popular_badge_and_others_do_not(): void
    {
        $this->car(['name' => 'Unit Pilihan', 'is_featured' => true]);
        $this->car(['name' => 'Unit Biasa', 'is_featured' => false]);

        $html = $this->get('/rental-mobil')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Populer'));
    }

    public function test_card_shows_status_price_specs_and_an_accessible_whatsapp_button(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281234567890');
        $this->car(['name' => 'Avanza Uji', 'status' => VehicleStatus::Rented, 'passenger_capacity' => 7], 350000);

        $this->get('/rental-mobil')->assertOk()
            ->assertSee('Avanza Uji')
            ->assertSee(VehicleStatus::Rented->label())
            ->assertSee('Mulai dari')
            ->assertSee('Rp 350.000')
            ->assertSee('7 kursi')
            ->assertSee('aria-label="Konsultasi Avanza Uji lewat WhatsApp"', false);
    }

    public function test_card_without_a_rate_asks_for_the_price_on_whatsapp(): void
    {
        $this->car(['name' => 'Tanpa Tarif'], null);

        $this->get('/rental-mobil')->assertOk()
            ->assertSee('Tanpa Tarif')
            ->assertSee('Tanyakan harga via WhatsApp')
            ->assertDontSee('Mulai dari');
    }

    public function test_card_falls_back_to_a_placeholder_without_a_photo(): void
    {
        $this->car(['name' => 'Tanpa Foto']);

        $this->get('/rental-mobil')->assertOk()->assertSee('Foto belum tersedia');
    }

    public function test_pagination_uses_the_indonesian_brand_view_with_prev_and_next_links(): void
    {
        foreach (range(1, 14) as $i) {
            $this->car(['name' => 'Unit '.$i]);
        }

        $first = $this->get('/rental-mobil')->assertOk()
            ->assertSee('Navigasi halaman')
            ->assertSee('rel="next"', false)
            ->assertSee('Berikutnya')
            ->assertDontSee('Pagination Navigation');
        $this->assertStringNotContainsString('rel="prev"', $first->getContent());

        $this->get('/rental-mobil?page=2')->assertOk()
            ->assertSee('rel="prev"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('Sebelumnya');
    }

    public function test_single_page_catalog_has_no_pagination(): void
    {
        $this->car();

        $this->get('/rental-mobil')->assertOk()->assertDontSee('Navigasi halaman');
    }

    public function test_home_lists_up_to_eight_featured_units(): void
    {
        foreach (range(1, 10) as $i) {
            $this->car(['name' => 'Pilihan '.$i, 'is_featured' => true, 'sort_order' => $i]);
        }

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(8, preg_match_all('/<article class="card card-hover/', $html));
    }
}
