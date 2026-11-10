<?php

namespace Tests\Feature;

use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleRate;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicShowroomTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attrs = [], ?int $price = 300000, ?VehicleCategory $cat = null): Vehicle
    {
        $cat ??= VehicleCategory::factory()->create();
        $v = Vehicle::factory()->create(['vehicle_category_id' => $cat->id] + $attrs);
        if ($price !== null) {
            VehicleRate::factory()->create(['vehicle_id' => $v->id, 'price_per_day' => $price]);
        }

        return $v;
    }

    private function motor(array $attrs = []): Vehicle
    {
        $cat = VehicleCategory::firstOrCreate(['slug' => 'motor'], ['name' => 'Motor', 'is_active' => true]);

        return $this->car($attrs, 100000, $cat);
    }

    public function test_pages_render_when_empty(): void
    {
        $this->get('/')->assertOk();
        $this->get('/rental-mobil')->assertOk();
    }

    public function test_home_shows_featured_vehicle(): void
    {
        $v = $this->car(['name' => 'Avanza Unggulan', 'is_featured' => true]);
        $this->get('/')->assertOk()->assertSee('Avanza Unggulan')->assertSee('Rp 300.000');
    }

    public function test_only_published_vehicles_are_listed(): void
    {
        $this->car(['name' => 'Tampil Satu']);
        $this->car(['name' => 'Disembunyikan', 'is_published' => false]);
        $gone = $this->car(['name' => 'Terhapus']);
        $gone->delete();

        $this->get('/rental-mobil')->assertSee('Tampil Satu')->assertDontSee('Disembunyikan')->assertDontSee('Terhapus');
    }

    public function test_unpublished_and_deleted_detail_is_404(): void
    {
        $hidden = $this->car(['is_published' => false]);
        $gone = $this->car();
        $gone->delete();

        $this->get('/rental-mobil/'.$hidden->slug)->assertNotFound();
        $this->get('/rental-mobil/'.$gone->slug)->assertNotFound();
        $this->get('/rental-mobil/tidak-ada')->assertNotFound();
    }

    public function test_inactive_category_hides_vehicles(): void
    {
        $cat = VehicleCategory::factory()->inactive()->create();
        $v = $this->car(['name' => 'Kategori Mati'], 200000, $cat);

        $this->get('/rental-mobil')->assertDontSee('Kategori Mati');
        $this->get('/rental-mobil/'.$v->slug)->assertNotFound();
    }

    public function test_status_does_not_hide_vehicle_and_shows_badge(): void
    {
        $v = $this->car(['name' => 'Lagi Jalan', 'status' => VehicleStatus::Rented]);

        $this->get('/rental-mobil')->assertSee('Lagi Jalan')->assertSee(VehicleStatus::Rented->label());
        $this->get('/rental-mobil/'.$v->slug)->assertOk()->assertSee(VehicleStatus::Rented->label());
    }

    public function test_detail_shows_rates_and_fee(): void
    {
        $v = $this->car(['name' => 'Detail Unit'], 450000);
        $this->get('/rental-mobil/'.$v->slug)->assertOk()->assertSee('Detail Unit')->assertSee('Rp 450.000');
    }

    public function test_filters(): void
    {
        $this->car(['name' => 'Matic Murah', 'transmission' => TransmissionType::Automatic, 'brand' => 'Honda', 'passenger_capacity' => 4], 200000);
        $this->car(['name' => 'Manual Mahal', 'transmission' => TransmissionType::Manual, 'brand' => 'Toyota', 'passenger_capacity' => 8], 800000);

        $this->get('/rental-mobil?transmission=manual')->assertSee('Manual Mahal')->assertDontSee('Matic Murah');
        $this->get('/rental-mobil?brand=Honda')->assertSee('Matic Murah')->assertDontSee('Manual Mahal');
        $this->get('/rental-mobil?min_capacity=6')->assertSee('Manual Mahal')->assertDontSee('Matic Murah');
        $this->get('/rental-mobil?max_price=300000')->assertSee('Matic Murah')->assertDontSee('Manual Mahal');
        $this->get('/rental-mobil?min_price=500000')->assertSee('Manual Mahal')->assertDontSee('Matic Murah');
        $this->get('/rental-mobil?q=mahal')->assertSee('Manual Mahal')->assertDontSee('Matic Murah');
    }

    public function test_category_filter(): void
    {
        $a = VehicleCategory::factory()->create();
        $this->car(['name' => 'Di Kategori A'], 100000, $a);
        $this->car(['name' => 'Di Kategori Lain']);

        $this->get('/rental-mobil?category='.$a->slug)->assertSee('Di Kategori A')->assertDontSee('Di Kategori Lain');
    }

    public function test_sorting_by_price(): void
    {
        $this->car(['name' => 'Aaa Mahal'], 900000);
        $this->car(['name' => 'Bbb Murah'], 100000);

        $this->get('/rental-mobil?sort=price_asc')->assertSeeInOrder(['Bbb Murah', 'Aaa Mahal']);
        $this->get('/rental-mobil?sort=price_desc')->assertSeeInOrder(['Aaa Mahal', 'Bbb Murah']);
    }

    public function test_invalid_query_params_are_ignored(): void
    {
        $this->car(['name' => 'Tetap Muncul']);

        $this->get('/rental-mobil?sort=hack&min_capacity=abc&min_price=-5&transmission=jet&category[]=x')
            ->assertOk()->assertSee('Tetap Muncul');
    }

    public function test_pagination(): void
    {
        for ($i = 0; $i < 14; $i++) {
            $this->car();
        }

        $this->get('/rental-mobil')->assertOk()->assertSee('1-12 dari 14', false);
        $this->get('/rental-mobil?page=2')->assertOk()->assertSee('13-14 dari 14', false);
    }

    public function test_motor_is_404_while_feature_disabled(): void
    {
        $m = $this->motor(['name' => 'Motor Matic X']);

        $this->get('/rental-motor')->assertNotFound();
        $this->get('/rental-motor/'.$m->slug)->assertNotFound();
        $this->get('/')->assertDontSee('/rental-motor', false);
    }

    public function test_motor_catalog_when_enabled(): void
    {
        WebsiteSetting::set('feature_motor_enabled', true);
        $m = $this->motor(['name' => 'Motor Matic X']);
        $c = $this->car(['name' => 'Mobil Biasa']);

        $this->get('/rental-motor')->assertOk()->assertSee('Motor Matic X')->assertDontSee('Mobil Biasa');
        $this->get('/rental-mobil')->assertOk()->assertSee('Mobil Biasa')->assertDontSee('Motor Matic X');
        $this->get('/rental-motor/'.$m->slug)->assertOk();
        $this->get('/rental-mobil/'.$m->slug)->assertNotFound();
        $this->get('/rental-motor/'.$c->slug)->assertNotFound();
        $this->get('/')->assertSee('/rental-motor', false);
    }

    public function test_whatsapp_link_encoded_when_number_valid(): void
    {
        WebsiteSetting::set('whatsapp_number', '+62 812-3456-789');
        $v = $this->car(['name' => 'Unit WA']);

        $this->get('/rental-mobil/'.$v->slug)->assertSee('https://wa.me/62812345678', false)->assertSee('text=', false);
    }

    public function test_whatsapp_link_absent_when_number_invalid(): void
    {
        WebsiteSetting::set('whatsapp_number', 'abc');
        $this->get('/')->assertOk()->assertDontSee('wa.me', false);
    }
}
