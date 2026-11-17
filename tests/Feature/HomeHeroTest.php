<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleRate;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attrs = []): Vehicle
    {
        $cat = VehicleCategory::factory()->create();
        $v = Vehicle::factory()->create(['vehicle_category_id' => $cat->id] + $attrs);
        VehicleRate::factory()->create(['vehicle_id' => $v->id, 'price_per_day' => 300000]);

        return $v;
    }

    public function test_hero_falls_back_to_the_bundled_photo_when_nothing_else_is_available(): void
    {
        $this->get('/')->assertOk()->assertSee('images/hero-default.jpg', false);
        $this->assertFileExists(public_path('images/hero-default.jpg'));
    }

    public function test_hero_title_highlights_only_the_last_word_and_is_escaped(): void
    {
        WebsiteSetting::set('hero_title', 'Sewa Mobil <b>Aman</b>');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<span class="text-accent">&lt;b&gt;Aman&lt;/b&gt;</span>', $html);
        $this->assertStringNotContainsString('<b>Aman</b>', $html);
    }

    public function test_stats_hide_when_there_are_no_units_and_no_admin_stats(): void
    {
        // Kartu statistik adalah <dl>; tanpa unit dan tanpa statistik admin, kartunya tidak dirender.
        $this->get('/')->assertOk()->assertDontSee('<dl', false);
    }

    public function test_stats_show_the_real_unit_count(): void
    {
        $this->car();
        $this->car();

        $this->get('/')->assertOk()->assertSee('Unit kendaraan')->assertSeeInOrder(['<dd', '2', '</dd>'], false);
    }

    public function test_admin_stat_needs_both_value_and_label(): void
    {
        WebsiteSetting::set('home_stat_1_value', '24/7');
        WebsiteSetting::set('home_stat_1_label', 'Siap melayani');
        WebsiteSetting::set('home_stat_2_value', '99');
        // statistik 2 tidak punya keterangan, jadi tidak boleh tampil

        $this->get('/')->assertOk()
            ->assertSee('24/7')
            ->assertSee('Siap melayani')
            ->assertDontSee('>99<', false);
    }

    public function test_stat_text_is_escaped(): void
    {
        WebsiteSetting::set('home_stat_1_value', '<i>9</i>');
        WebsiteSetting::set('home_stat_1_label', 'Label');

        $this->get('/')->assertOk()->assertDontSee('<i>9</i>', false);
    }

    public function test_home_search_sends_filters_to_the_catalog_and_has_a_labelled_form(): void
    {
        $this->car(['brand' => 'Toyota']);

        $this->get('/')->assertOk()
            ->assertSee('role="search"', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="brand"', false)
            ->assertSee('name="min_capacity"', false)
            ->assertSee('<option value="Toyota">Toyota</option>', false);
    }
}
