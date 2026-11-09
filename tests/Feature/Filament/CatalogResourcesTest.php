<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AdditionalFees\Pages\CreateAdditionalFee;
use App\Filament\Resources\VehicleCategories\Pages\CreateVehicleCategory;
use App\Filament\Resources\VehicleCategories\Pages\EditVehicleCategory;
use App\Filament\Resources\VehicleCategories\Pages\ListVehicleCategories;
use App\Models\AdditionalFee;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_category_and_slug_is_generated_from_name(): void
    {
        Livewire::test(CreateVehicleCategory::class)
            ->fillForm(['name' => 'Mobil Keluarga', 'sort_order' => 5, 'is_active' => true])
            ->assertFormSet(['slug' => 'mobil-keluarga'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vehicle_categories', ['slug' => 'mobil-keluarga', 'sort_order' => 5]);
    }

    public function test_category_slug_must_be_unique(): void
    {
        VehicleCategory::factory()->create(['slug' => 'sama']);

        Livewire::test(CreateVehicleCategory::class)
            ->fillForm(['name' => 'Lain', 'slug' => 'sama'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_motor_category_slug_is_locked(): void
    {
        $motor = VehicleCategory::factory()->create(['name' => 'Motor', 'slug' => VehicleCategory::MOTOR_SLUG]);

        Livewire::test(EditVehicleCategory::class, ['record' => $motor->getKey()])
            ->assertFormFieldIsDisabled('slug');
    }

    public function test_category_with_vehicles_cannot_be_deleted(): void
    {
        $category = VehicleCategory::factory()->create();
        Vehicle::factory()->create(['vehicle_category_id' => $category->id]);

        Livewire::test(ListVehicleCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseHas('vehicle_categories', ['id' => $category->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = VehicleCategory::factory()->create();

        Livewire::test(ListVehicleCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('vehicle_categories', ['id' => $category->id]);
    }

    public function test_can_create_additional_fee(): void
    {
        Livewire::test(CreateAdditionalFee::class)
            ->fillForm(['name' => 'Overtime driver', 'amount' => 50000, 'sort_order' => 1, 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(50000, AdditionalFee::firstWhere('name', 'Overtime driver')->amount);
    }

    public function test_additional_fee_amount_cannot_be_negative(): void
    {
        Livewire::test(CreateAdditionalFee::class)
            ->fillForm(['name' => 'X', 'amount' => -1])
            ->call('create')
            ->assertHasFormErrors(['amount' => 'min']);
    }
}
