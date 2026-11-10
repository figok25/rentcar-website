<?php

namespace Tests\Feature;

use App\Enums\RentalServiceType;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleRate;
use App\Models\WebsiteSetting;
use App\Services\WhatsAppMessageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class WhatsAppInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(array $attrs = []): Vehicle
    {
        $v = Vehicle::factory()->create(['name' => 'Avanza Veloz', 'vehicle_category_id' => VehicleCategory::factory()] + $attrs);
        VehicleRate::factory()->create(['vehicle_id' => $v->id, 'service_type' => RentalServiceType::SelfDrive, 'price_per_day' => 350000]);

        return $v;
    }

    private function payload(Vehicle $v, array $over = []): array
    {
        return array_replace([
            'vehicle' => $v->slug,
            'customer_name' => 'Budi Santoso',
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'service_type' => 'self_drive',
            'pickup_location' => 'Bandara',
            'notes' => 'Butuh kursi bayi',
        ], $over);
    }

    public function test_days_counts_minimum_one(): void
    {
        $this->assertSame(1, WhatsAppMessageBuilder::days('2026-12-01', '2026-12-01'));
        $this->assertSame(3, WhatsAppMessageBuilder::days('2026-12-01', '2026-12-04'));
    }

    public function test_message_builder_output(): void
    {
        WebsiteSetting::set('business_name', 'Rental Maju');
        $v = $this->vehicle();

        $text = app(WhatsAppMessageBuilder::class)->build($v, [
            'customer_name' => "Budi\nSantoso",
            'start_date' => '2026-12-15',
            'end_date' => '2026-12-18',
            'service_type' => 'self_drive',
            'notes' => "baris satu\n\nbaris dua",
        ]);

        $this->assertStringContainsString('Halo Rental Maju', $text);
        $this->assertStringContainsString('Unit: Avanza Veloz', $text);
        $this->assertStringContainsString('Layanan: Lepas kunci', $text);
        $this->assertStringContainsString('Tanggal ambil: Selasa, 15 Desember 2026', $text);
        $this->assertStringContainsString('Durasi: 3 hari', $text);
        $this->assertStringContainsString('Nama: Budi Santoso', $text);
        $this->assertStringContainsString('Catatan: baris satu baris dua', $text);
    }

    public function test_valid_submission_redirects_to_wa_me_with_encoded_message(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281234567890');
        $v = $this->vehicle();

        $response = $this->post(route('inquiry.store'), $this->payload($v));

        $response->assertRedirectContains('https://wa.me/6281234567890?text=');
        $url = $response->headers->get('Location');
        $text = rawurldecode(explode('text=', $url)[1]);
        $this->assertStringContainsString('Avanza Veloz', $text);
        $this->assertStringContainsString('Durasi: 3 hari', $text);
        $this->assertStringNotContainsString("\r", $url);
    }

    public function test_required_fields_and_date_rules(): void
    {
        $v = $this->vehicle();

        $this->post(route('inquiry.store'), ['vehicle' => $v->slug])
            ->assertSessionHasErrors(['customer_name', 'start_date', 'end_date']);

        $this->post(route('inquiry.store'), $this->payload($v, ['start_date' => now()->subDay()->format('Y-m-d')]))
            ->assertSessionHasErrors('start_date');

        $this->post(route('inquiry.store'), $this->payload($v, ['end_date' => now()->format('Y-m-d')]))
            ->assertSessionHasErrors('end_date');

        $this->post(route('inquiry.store'), $this->payload($v, ['end_date' => now()->addDays(200)->format('Y-m-d')]))
            ->assertSessionHasErrors('end_date');

        $this->post(route('inquiry.store'), $this->payload($v, ['start_date' => '15/12/2026']))
            ->assertSessionHasErrors('start_date');
    }

    public function test_service_type_must_be_valid_and_offered(): void
    {
        $v = $this->vehicle();

        $this->post(route('inquiry.store'), $this->payload($v, ['service_type' => 'hack']))
            ->assertSessionHasErrors('service_type');

        $this->post(route('inquiry.store'), $this->payload($v, ['service_type' => 'with_driver']))
            ->assertSessionHasErrors('service_type');
    }

    public function test_honeypot_blocks_bots(): void
    {
        $v = $this->vehicle();

        $this->post(route('inquiry.store'), $this->payload($v, ['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');
    }

    public function test_hidden_or_unknown_vehicle_is_404(): void
    {
        $hidden = $this->vehicle(['is_published' => false]);

        $this->post(route('inquiry.store'), $this->payload($hidden))->assertNotFound();
        $this->post(route('inquiry.store'), $this->payload($hidden, ['vehicle' => 'tidak-ada']))->assertNotFound();
    }

    public function test_invalid_whatsapp_number_gives_error_and_no_redirect_away(): void
    {
        WebsiteSetting::set('whatsapp_number', 'abc');
        $v = $this->vehicle();

        $this->post(route('inquiry.store'), $this->payload($v))->assertSessionHasErrors('whatsapp');
    }

    public function test_requests_are_rate_limited(): void
    {
        RateLimiter::clear('inquiry');
        $v = $this->vehicle();

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('inquiry.store'), $this->payload($v))->assertRedirect();
        }

        $this->post(route('inquiry.store'), $this->payload($v))->assertStatus(429);
    }

    public function test_detail_page_shows_form_only_with_valid_number(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281234567890');
        $v = $this->vehicle();

        $this->get('/rental-mobil/'.$v->slug)
            ->assertSee('Rencanakan sewa')
            ->assertSee(route('inquiry.store'), false)
            ->assertSee('name="website"', false);

        WebsiteSetting::set('whatsapp_number', '');
        $this->get('/rental-mobil/'.$v->slug)->assertOk()->assertDontSee('Rencanakan sewa');
    }

    public function test_old_input_is_kept_after_validation_error(): void
    {
        WebsiteSetting::set('whatsapp_number', '6281234567890');
        $v = $this->vehicle();

        $this->from('/rental-mobil/'.$v->slug)
            ->post(route('inquiry.store'), $this->payload($v, ['end_date' => '']))
            ->assertRedirect('/rental-mobil/'.$v->slug.'#form-sewa');

        $this->get('/rental-mobil/'.$v->slug)->assertSee('Budi Santoso');
    }
}
