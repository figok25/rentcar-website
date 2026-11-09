<?php

namespace Tests\Feature;

use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_the_stored_value_or_the_default(): void
    {
        WebsiteSetting::set('business_name', 'Rental Contoh');

        $this->assertSame('Rental Contoh', WebsiteSetting::get('business_name'));
        $this->assertNull(WebsiteSetting::get('missing'));
        $this->assertSame('fallback', WebsiteSetting::get('missing', 'fallback'));
    }

    public function test_empty_values_fall_back_to_the_default(): void
    {
        WebsiteSetting::set('phone_number', '');

        $this->assertSame('-', WebsiteSetting::get('phone_number', '-'));
    }

    public function test_bool_reads_flags_stored_as_text(): void
    {
        WebsiteSetting::set('feature_motor_enabled', true);
        WebsiteSetting::set('feature_tours_enabled', false);

        $this->assertTrue(WebsiteSetting::bool('feature_motor_enabled'));
        $this->assertFalse(WebsiteSetting::bool('feature_tours_enabled'));
        $this->assertFalse(WebsiteSetting::bool('feature_travel_enabled'));
        $this->assertTrue(WebsiteSetting::bool('feature_travel_enabled', true));
    }

    public function test_cache_is_refreshed_when_a_setting_changes(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281200000001');
        $this->assertSame('6281200000001', WebsiteSetting::get('whatsapp_number'));

        WebsiteSetting::set('whatsapp_number', '6281200000002');
        $this->assertSame('6281200000002', WebsiteSetting::get('whatsapp_number'));

        WebsiteSetting::query()->where('key', 'whatsapp_number')->first()->delete();
        $this->assertNull(WebsiteSetting::get('whatsapp_number'));
    }

    public function test_set_updates_an_existing_key_instead_of_duplicating_it(): void
    {
        WebsiteSetting::set('address', 'Alamat lama', 'contact');
        WebsiteSetting::set('address', 'Alamat baru');

        $this->assertSame(1, WebsiteSetting::query()->where('key', 'address')->count());
        $this->assertSame('contact', WebsiteSetting::query()->where('key', 'address')->value('group'));
        $this->assertSame('Alamat baru', WebsiteSetting::get('address'));
    }
}
