<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageWebsiteSettings;
use App\Filament\Resources\Advantages\Pages\CreateAdvantage;
use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\Mottos\Pages\CreateMotto;
use App\Filament\Resources\RentalRequirementGroups\Pages\CreateRentalRequirementGroup;
use App\Filament\Resources\RentalRequirementGroups\Pages\EditRentalRequirementGroup;
use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Models\Faq;
use App\Models\RentalRequirementGroup;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsAndContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function validSettings(array $over = []): array
    {
        return array_replace([
            'business_name' => 'Rental Maju',
            'whatsapp_number' => '0812-3456-7890',
            'feature_motor_enabled' => false,
            'feature_tours_enabled' => false,
            'feature_travel_enabled' => false,
        ], $over);
    }

    public function test_settings_page_requires_login_and_renders(): void
    {
        auth()->logout();
        $this->get('/admin/pengaturan-website')->assertRedirect();

        $this->actingAs(User::factory()->create());
        $this->get('/admin/pengaturan-website')->assertOk();
    }

    public function test_settings_form_is_prefilled(): void
    {
        WebsiteSetting::set('business_name', 'Nama Lama');

        Livewire::test(ManageWebsiteSettings::class)->assertFormSet(['business_name' => 'Nama Lama']);
    }

    public function test_save_normalizes_whatsapp_and_updates_public_site(): void
    {
        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings())
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('6281234567890', WebsiteSetting::get('whatsapp_number'));
        $this->assertSame('Rental Maju', WebsiteSetting::get('business_name'));
        $this->get('/')->assertSee('https://wa.me/6281234567890', false)->assertSee('Rental Maju');
    }

    public function test_invalid_whatsapp_is_rejected(): void
    {
        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings(['whatsapp_number' => '123']))
            ->call('save')
            ->assertHasFormErrors(['whatsapp_number']);
    }

    public function test_business_name_and_whatsapp_are_required(): void
    {
        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings(['business_name' => '', 'whatsapp_number' => '']))
            ->call('save')
            ->assertHasFormErrors(['business_name' => 'required', 'whatsapp_number' => 'required']);
    }

    public function test_maps_iframe_is_reduced_to_src_and_foreign_hosts_rejected(): void
    {
        $iframe = '<iframe src="https://www.google.com/maps/embed?pb=abc&amp;z=1" width="600"></iframe>';

        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings(['maps_embed_url' => $iframe]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('https://www.google.com/maps/embed?pb=abc&z=1', WebsiteSetting::get('maps_embed_url'));

        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings(['maps_embed_url' => '<iframe src="https://evil.example/x"></iframe>']))
            ->call('save')
            ->assertHasFormErrors(['maps_embed_url']);
    }

    public function test_motor_toggle_controls_public_motor_page(): void
    {
        $this->get('/rental-motor')->assertNotFound();

        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings(['feature_motor_enabled' => true]))
            ->call('save');

        $this->get('/rental-motor')->assertOk();

        Livewire::test(ManageWebsiteSettings::class)
            ->fillForm($this->validSettings(['feature_motor_enabled' => false]))
            ->call('save');

        $this->get('/rental-motor')->assertNotFound();
    }

    public function test_can_create_simple_content(): void
    {
        Livewire::test(CreateMotto::class)->fillForm(['title' => 'Unit terawat', 'sort_order' => 1])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateAdvantage::class)->fillForm(['title' => 'Respons cepat', 'sort_order' => 1])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateFaq::class)->fillForm(['question' => 'Apa saja syaratnya?', 'answer' => 'KTP dan SIM.', 'sort_order' => 1])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateTestimonial::class)->fillForm(['customer_display_name' => 'Budi', 'content' => 'Mantap.', 'rating' => 5, 'sort_order' => 1])->call('create')->assertHasNoFormErrors();

        $this->assertDatabaseHas('mottos', ['title' => 'Unit terawat']);
        $this->assertDatabaseHas('advantages', ['title' => 'Respons cepat']);
        $this->assertDatabaseHas('faqs', ['question' => 'Apa saja syaratnya?']);
        $this->assertDatabaseHas('testimonials', ['customer_display_name' => 'Budi', 'rating' => 5]);
    }

    public function test_required_fields_validated(): void
    {
        Livewire::test(CreateFaq::class)->fillForm(['question' => '', 'answer' => ''])->call('create')
            ->assertHasFormErrors(['question' => 'required', 'answer' => 'required']);

        Livewire::test(CreateTestimonial::class)->fillForm(['customer_display_name' => '', 'content' => ''])->call('create')
            ->assertHasFormErrors(['customer_display_name' => 'required', 'content' => 'required']);
    }

    public function test_faq_list_and_delete(): void
    {
        $faq = Faq::factory()->create();

        Livewire::test(ListFaqs::class)->assertCanSeeTableRecords([$faq])->callTableBulkAction('delete', [$faq]);

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_requirement_group_with_requirements(): void
    {
        Livewire::test(CreateRentalRequirementGroup::class)
            ->fillForm([
                'name' => 'Syarat Lepas Kunci',
                'sort_order' => 1,
                'requirements' => [
                    ['requirement_text' => 'KTP asli', 'is_active' => true],
                    ['requirement_text' => 'SIM A aktif', 'is_active' => true],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $group = RentalRequirementGroup::query()->where('name', 'Syarat Lepas Kunci')->firstOrFail();
        $this->assertSame(['KTP asli', 'SIM A aktif'], $group->requirements()->pluck('requirement_text')->all());

        $this->get('/syarat-sewa')->assertSee('KTP asli')->assertSee('SIM A aktif');
    }

    public function test_requirement_text_is_required(): void
    {
        Livewire::test(CreateRentalRequirementGroup::class)
            ->fillForm(['name' => 'X', 'sort_order' => 0, 'requirements' => [['requirement_text' => '']]])
            ->call('create')
            ->assertHasFormErrors();
    }

    public function test_can_edit_group_without_losing_requirements(): void
    {
        $group = RentalRequirementGroup::factory()->create(['name' => 'Lama']);
        $group->requirements()->create(['requirement_text' => 'Satu', 'sort_order' => 1]);

        Livewire::test(EditRentalRequirementGroup::class, ['record' => $group->getRouteKey()])
            ->fillForm(['name' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $group->fresh()->name);
        $this->assertSame(1, $group->requirements()->count());
    }
}
