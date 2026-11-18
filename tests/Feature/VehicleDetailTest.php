<?php

namespace Tests\Feature;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleRate;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleDetailTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attrs = [], ?int $price = 450000): Vehicle
    {
        $cat = VehicleCategory::firstOrCreate(['slug' => 'mobil-kota'], ['name' => 'Mobil Kota', 'is_active' => true]);
        $v = Vehicle::factory()->create(['vehicle_category_id' => $cat->id] + $attrs);
        if ($price !== null) {
            VehicleRate::factory()->create(['vehicle_id' => $v->id, 'price_per_day' => $price]);
        }

        return $v;
    }

    public function test_detail_page_uses_brand_styling_and_no_legacy_plate_tokens(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281234567890');
        $v = $this->car(['name' => 'Detail Uji', 'passenger_capacity' => 7, 'is_featured' => true]);

        $html = $this->get($v->publicUrl())->assertOk()
            ->assertSee('Detail Uji')
            ->assertSee('Spesifikasi')
            ->assertSee('Harga sewa')
            ->assertSee('Rp 450.000')
            ->assertSee('7 penumpang')
            ->assertSee('Populer')
            ->assertSee('Rencanakan sewa')
            ->getContent();

        $this->assertStringNotContainsString('plate', $html);
    }

    public function test_detail_without_rate_asks_for_price_on_whatsapp(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281234567890');
        $v = $this->car(['name' => 'Tanpa Harga'], null);

        $this->get($v->publicUrl())->assertOk()
            ->assertSee('Harga belum dicantumkan');
    }

    public function test_detail_shows_status_and_hides_form_without_whatsapp_number(): void
    {
        $v = $this->car(['name' => 'Sedang Jalan', 'status' => VehicleStatus::Rented]);

        $this->get($v->publicUrl())->assertOk()
            ->assertSee(VehicleStatus::Rented->label());
    }
}
