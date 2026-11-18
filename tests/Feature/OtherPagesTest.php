<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TravelService;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtherPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tour_pages_have_red_price_and_no_legacy_plate(): void
    {
        WebsiteSetting::set('feature_tours_enabled', true);
        $tour = Tour::factory()->create(['title' => 'Paket Uji', 'price' => 750000]);

        foreach (['/paket-wisata', $tour->publicUrl()] as $url) {
            $html = $this->get($url)->assertOk()->assertSee('Paket Uji')->assertSee('Rp 750.000')->getContent();
            $this->assertStringContainsString('text-accent', $html);
            $this->assertStringNotContainsString('plate', $html);
        }
    }

    public function test_travel_pages_show_price_label_without_legacy_plate(): void
    {
        WebsiteSetting::set('feature_travel_enabled', true);
        $service = TravelService::factory()->create(['title' => 'Travel Uji', 'price_label' => 'Rp 150.000 / kursi']);

        foreach (['/travel', $service->publicUrl()] as $url) {
            $html = $this->get($url)->assertOk()->assertSee('Travel Uji')->assertSee('Rp 150.000 / kursi')->getContent();
            $this->assertStringNotContainsString('plate', $html);
        }
    }

    public function test_static_pages_and_404_have_no_legacy_plate(): void
    {
        foreach (['/syarat-sewa', '/kontak'] as $url) {
            $this->assertStringNotContainsString('plate', $this->get($url)->assertOk()->getContent());
        }

        $html = $this->get('/tidak-ada-halaman')->assertNotFound()->assertSee('Halaman tidak ditemukan')->getContent();
        $this->assertStringNotContainsString('plate', $html);
    }

    public function test_server_error_views_are_branded(): void
    {
        foreach (['500', '503'] as $code) {
            $html = view('errors.'.$code)->render();
            $this->assertStringContainsString('#b02e36', $html);
            $this->assertStringNotContainsString('plate', $html);
        }
    }
}
