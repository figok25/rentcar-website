<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\RentalRequirement;
use App\Models\RentalRequirementGroup;
use App\Models\Testimonial;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hides_faq_and_testimonial_sections_when_empty(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Kata pelanggan')->assertDontSee('Pertanyaan yang sering diajukan');
    }

    public function test_home_shows_only_active_faq_and_published_testimonials(): void
    {
        Faq::factory()->create(['question' => 'Apakah bisa lepas kunci?', 'answer' => 'Bisa, dengan syarat.', 'is_active' => true]);
        Faq::factory()->create(['question' => 'Pertanyaan tersembunyi', 'is_active' => false]);
        Testimonial::factory()->create(['customer_display_name' => 'Sari', 'content' => 'Unit bersih.', 'rating' => 4, 'is_published' => true]);
        Testimonial::factory()->create(['customer_display_name' => 'Rahasia', 'is_published' => false]);

        $this->get('/')->assertOk()
            ->assertSee('Apakah bisa lepas kunci?')
            ->assertDontSee('Pertanyaan tersembunyi')
            ->assertSee('Unit bersih.')
            ->assertSee('Bintang 4 dari 5')
            ->assertDontSee('Rahasia');
    }

    public function test_faq_and_testimonial_content_is_escaped(): void
    {
        Faq::factory()->create(['question' => '<script>alert(1)</script>', 'answer' => '<b>x</b>']);

        $this->get('/')->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<b>x</b>', false);
    }

    public function test_requirements_page_empty_state(): void
    {
        $this->get('/syarat-sewa')->assertOk()->assertSee('belum dipublikasikan');
    }

    public function test_requirements_page_lists_active_groups_with_active_items(): void
    {
        $g = RentalRequirementGroup::factory()->create(['name' => 'Syarat Lepas Kunci', 'is_active' => true]);
        RentalRequirement::factory()->create(['rental_requirement_group_id' => $g->id, 'requirement_text' => 'KTP asli', 'is_active' => true]);
        RentalRequirement::factory()->create(['rental_requirement_group_id' => $g->id, 'requirement_text' => 'Syarat nonaktif', 'is_active' => false]);

        $off = RentalRequirementGroup::factory()->create(['name' => 'Grup Mati', 'is_active' => false]);
        RentalRequirement::factory()->create(['rental_requirement_group_id' => $off->id, 'requirement_text' => 'Tidak tampil']);

        $empty = RentalRequirementGroup::factory()->create(['name' => 'Grup Kosong', 'is_active' => true]);

        $this->get('/syarat-sewa')->assertOk()
            ->assertSee('Syarat Lepas Kunci')->assertSee('KTP asli')
            ->assertDontSee('Syarat nonaktif')->assertDontSee('Grup Mati')->assertDontSee('Grup Kosong');
    }

    public function test_category_specific_requirements_only_on_matching_vehicle(): void
    {
        $a = VehicleCategory::factory()->create();
        $b = VehicleCategory::factory()->create();
        $g = RentalRequirementGroup::factory()->create(['name' => 'Khusus Kategori A', 'vehicle_category_id' => $a->id]);
        RentalRequirement::factory()->create(['rental_requirement_group_id' => $g->id, 'requirement_text' => 'Syarat khusus A']);

        $va = Vehicle::factory()->create(['vehicle_category_id' => $a->id]);
        $vb = Vehicle::factory()->create(['vehicle_category_id' => $b->id]);

        $this->get('/rental-mobil/'.$va->slug)->assertSee('Syarat khusus A');
        $this->get('/rental-mobil/'.$vb->slug)->assertDontSee('Syarat khusus A');
    }

    public function test_contact_page_shows_info_and_only_google_maps_embed(): void
    {
        WebsiteSetting::set('address', 'Jl. Contoh No. 1');
        WebsiteSetting::set('phone_number', '0311234567');
        WebsiteSetting::set('maps_embed_url', 'https://www.google.com/maps/embed?pb=abc');

        $this->get('/kontak')->assertOk()
            ->assertSee('Jl. Contoh No. 1')->assertSee('0311234567')
            ->assertSee('https://www.google.com/maps/embed?pb=abc', false);

        WebsiteSetting::set('maps_embed_url', 'https://evil.example/frame');
        $this->get('/kontak')->assertOk()->assertDontSee('evil.example', false)->assertDontSee('<iframe', false);
    }

    public function test_menu_links_exist(): void
    {
        $this->get('/')->assertSee(route('requirements'), false)->assertSee(route('contact'), false);
    }
}
