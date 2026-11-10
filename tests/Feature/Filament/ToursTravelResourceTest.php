<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Tours\Pages\CreateTour;
use App\Filament\Resources\Tours\Pages\EditTour;
use App\Filament\Resources\Tours\Pages\ListTours;
use App\Filament\Resources\TravelServices\Pages\CreateTravelService;
use App\Filament\Resources\TravelServices\Pages\ListTravelServices;
use App\Models\Tour;
use App\Models\TravelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ToursTravelResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_pages_render(): void
    {
        $this->get('/admin/tours')->assertOk();
        $this->get('/admin/tours/create')->assertOk();
        $this->get('/admin/travel')->assertOk();
        $this->get('/admin/travel/create')->assertOk();
    }

    public function test_create_tour_generates_slug(): void
    {
        Livewire::test(CreateTour::class)
            ->fillForm(['title' => 'Wisata Bromo Sunrise', 'price' => 750000, 'sort_order' => 1, 'is_published' => true])
            ->assertFormSet(['slug' => 'wisata-bromo-sunrise'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tours', ['slug' => 'wisata-bromo-sunrise', 'price' => 750000, 'is_published' => true]);
    }

    public function test_tour_validation(): void
    {
        Tour::factory()->create(['slug' => 'sama']);

        Livewire::test(CreateTour::class)
            ->fillForm(['title' => '', 'slug' => ''])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'slug' => 'required']);

        Livewire::test(CreateTour::class)
            ->fillForm(['title' => 'X', 'slug' => 'sama', 'price' => -5])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique', 'price']);
    }

    public function test_tour_with_photos_gets_exactly_one_primary(): void
    {
        Storage::fake('public');

        Livewire::test(CreateTour::class)
            ->fillForm([
                'title' => 'Paket Foto',
                'sort_order' => 0,
                'images' => [
                    ['image_path' => [UploadedFile::fake()->image('a.jpg')], 'is_primary' => false],
                    ['image_path' => [UploadedFile::fake()->image('b.jpg')], 'is_primary' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $tour = Tour::query()->where('slug', 'paket-foto')->firstOrFail();
        $this->assertSame(2, $tour->images()->count());
        $this->assertSame(1, $tour->images()->where('is_primary', true)->count());
        foreach ($tour->images as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
    }

    public function test_edit_and_list_tour(): void
    {
        $tour = Tour::factory()->create(['title' => 'Lama']);

        Livewire::test(EditTour::class, ['record' => $tour->getRouteKey()])
            ->fillForm(['title' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $tour->fresh()->title);
        Livewire::test(ListTours::class)->assertCanSeeTableRecords([$tour]);
    }

    public function test_create_travel_service(): void
    {
        Livewire::test(CreateTravelService::class)
            ->fillForm(['title' => 'Travel Sby Mlg', 'route_label' => 'Surabaya - Malang', 'sort_order' => 0, 'is_published' => true])
            ->assertFormSet(['slug' => 'travel-sby-mlg'])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = TravelService::query()->where('slug', 'travel-sby-mlg')->firstOrFail();
        Livewire::test(ListTravelServices::class)->assertCanSeeTableRecords([$service]);
    }
}
