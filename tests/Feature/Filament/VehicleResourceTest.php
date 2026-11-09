<?php

namespace Tests\Feature\Filament;

use App\Enums\RentalServiceType;
use App\Enums\VehicleStatus;
use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Filament\Widgets\VehicleStatusOverview;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use App\Models\VehicleRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VehicleResourceTest extends TestCase
{
    use RefreshDatabase;

    private VehicleCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->category = VehicleCategory::factory()->create();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'vehicle_category_id' => $this->category->id,
            'name' => 'Toyota Avanza 2022',
            'transmission' => 'manual',
            'fuel_type' => 'gasoline',
            'passenger_capacity' => 7,
            'status' => 'available',
            'sort_order' => 0,
            'is_published' => true,
        ], $overrides);
    }

    public function test_can_create_vehicle_with_generated_slug(): void
    {
        Livewire::test(CreateVehicle::class)
            ->fillForm($this->validData())
            ->assertFormSet(['slug' => 'toyota-avanza-2022'])
            ->call('create')
            ->assertHasNoFormErrors();

        $vehicle = Vehicle::firstWhere('slug', 'toyota-avanza-2022');
        $this->assertNotNull($vehicle);
        $this->assertSame(VehicleStatus::Available, $vehicle->status);
        $this->assertTrue($vehicle->is_published);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::test(CreateVehicle::class)
            ->fillForm(['name' => '', 'vehicle_category_id' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'vehicle_category_id' => 'required']);
    }

    public function test_slug_must_be_unique(): void
    {
        Vehicle::factory()->create(['slug' => 'sudah-ada']);

        Livewire::test(CreateVehicle::class)
            ->fillForm($this->validData(['slug' => 'sudah-ada']))
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_can_save_rates_for_both_service_types(): void
    {
        Livewire::test(CreateVehicle::class)
            ->fillForm($this->validData([
                'rates' => [
                    ['service_type' => 'self_drive', 'price_per_day' => 350000, 'is_active' => true],
                    ['service_type' => 'with_driver', 'price_per_day' => 550000, 'includes_driver' => true, 'is_active' => true],
                ],
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $vehicle = Vehicle::firstWhere('slug', 'toyota-avanza-2022');
        $this->assertCount(2, $vehicle->rates);
        $this->assertSame(350000, $vehicle->rates->firstWhere('service_type', RentalServiceType::SelfDrive)->price_per_day);
        $this->assertTrue($vehicle->rates->firstWhere('service_type', RentalServiceType::WithDriver)->includes_driver);
    }

    public function test_duplicate_service_type_is_rejected(): void
    {
        Livewire::test(CreateVehicle::class)
            ->fillForm($this->validData([
                'rates' => [
                    ['service_type' => 'self_drive', 'price_per_day' => 300000],
                    ['service_type' => 'self_drive', 'price_per_day' => 400000],
                ],
            ]))
            ->call('create')
            ->assertHasFormErrors();

        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_negative_price_is_rejected(): void
    {
        Livewire::test(CreateVehicle::class)
            ->fillForm($this->validData(['rates' => [['service_type' => 'self_drive', 'price_per_day' => -5]]]))
            ->call('create')
            ->assertHasFormErrors();
    }

    public function test_can_upload_photos_and_first_becomes_primary_when_none_selected(): void
    {
        Storage::fake('public');

        Livewire::test(CreateVehicle::class)
            ->fillForm($this->validData([
                'images' => [
                    ['image_path' => [UploadedFile::fake()->image('depan.jpg')], 'is_primary' => false],
                    ['image_path' => [UploadedFile::fake()->image('samping.jpg')], 'is_primary' => false],
                ],
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $vehicle = Vehicle::firstWhere('slug', 'toyota-avanza-2022');
        $this->assertCount(2, $vehicle->images);
        $this->assertSame(1, $vehicle->images->where('is_primary', true)->count());
        $this->assertTrue($vehicle->images->first()->is_primary);
        $this->assertSame([1, 2], $vehicle->images->pluck('sort_order')->all());

        foreach ($vehicle->images as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
    }

    public function test_multiple_primary_flags_are_normalized_to_one(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleImage::factory()->count(3)->create(['vehicle_id' => $vehicle->id, 'is_primary' => true]);

        $vehicle->normalizePrimaryImage();

        $this->assertSame(1, $vehicle->images()->where('is_primary', true)->count());
    }

    public function test_deleting_photo_removes_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('vehicles/x.jpg', 'data');
        $image = VehicleImage::factory()->create(['image_path' => 'vehicles/x.jpg']);

        $image->delete();

        Storage::disk('public')->assertMissing('vehicles/x.jpg');
    }

    public function test_edit_updates_status_and_keeps_existing_rates(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => 'available']);
        VehicleRate::factory()->create(['vehicle_id' => $vehicle->id, 'service_type' => 'self_drive', 'price_per_day' => 250000]);

        Livewire::test(EditVehicle::class, ['record' => $vehicle->getKey()])
            ->fillForm(['status' => 'maintenance'])
            ->call('save')
            ->assertHasNoFormErrors();

        $vehicle->refresh();
        $this->assertSame(VehicleStatus::Maintenance, $vehicle->status);
        $this->assertCount(1, $vehicle->rates);
    }

    public function test_status_can_be_changed_inline_from_table(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => 'available']);

        Livewire::test(ListVehicles::class)
            ->call('updateTableColumnState', 'status', $vehicle->getKey(), 'rented');

        $this->assertSame(VehicleStatus::Rented, $vehicle->refresh()->status);
    }

    public function test_publish_can_be_toggled_inline_and_status_does_not_affect_publication(): void
    {
        $vehicle = Vehicle::factory()->create(['is_published' => false, 'status' => 'available']);

        Livewire::test(ListVehicles::class)
            ->call('updateTableColumnState', 'is_published', $vehicle->getKey(), true)
            ->call('updateTableColumnState', 'status', $vehicle->getKey(), 'rented');

        $vehicle->refresh();
        $this->assertTrue($vehicle->is_published);
        $this->assertSame(VehicleStatus::Rented, $vehicle->status);
    }

    public function test_table_filters_by_status_and_category(): void
    {
        $other = VehicleCategory::factory()->create();
        $a = Vehicle::factory()->create(['status' => 'available', 'vehicle_category_id' => $this->category->id]);
        $b = Vehicle::factory()->create(['status' => 'rented', 'vehicle_category_id' => $other->id]);

        Livewire::test(ListVehicles::class)
            ->assertCanSeeTableRecords([$a, $b])
            ->filterTable('status', 'rented')
            ->assertCanSeeTableRecords([$b])
            ->assertCanNotSeeTableRecords([$a])
            ->removeTableFilters()
            ->filterTable('vehicle_category_id', $this->category->id)
            ->assertCanSeeTableRecords([$a])
            ->assertCanNotSeeTableRecords([$b]);
    }

    public function test_soft_deleted_vehicle_can_be_found_with_trashed_filter_and_restored(): void
    {
        $vehicle = Vehicle::factory()->create();
        $vehicle->delete();

        Livewire::test(ListVehicles::class)
            ->assertCanNotSeeTableRecords([$vehicle])
            ->filterTable('trashed', true)
            ->assertCanSeeTableRecords([$vehicle]);

        Livewire::test(EditVehicle::class, ['record' => $vehicle->getKey()])
            ->callAction('restore');

        $this->assertNull($vehicle->refresh()->deleted_at);
    }

    public function test_dashboard_widget_counts_vehicles_per_status(): void
    {
        Vehicle::factory()->count(2)->create(['status' => 'available']);
        Vehicle::factory()->create(['status' => 'rented']);
        Vehicle::factory()->create(['status' => 'maintenance']);
        Vehicle::factory()->create(['status' => 'rented'])->delete();

        Livewire::test(VehicleStatusOverview::class)
            ->assertSee('Tersedia')
            ->assertSee('Sedang disewa')
            ->assertSee('Maintenance')
            ->assertSee('Tidak tersedia');

        $stats = (new \ReflectionMethod(VehicleStatusOverview::class, 'getStats'))
            ->invoke(new VehicleStatusOverview);
        $values = collect($stats)->map(fn ($s) => $s->getValue())->all();
        $this->assertSame([2, 1, 1, 0], $values);
    }
}
