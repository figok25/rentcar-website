<?php

namespace Tests\Feature;

use App\Enums\RentalServiceType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use App\Models\VehicleRate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_scope_returns_only_published_vehicles(): void
    {
        $published = Vehicle::factory()->create();
        Vehicle::factory()->unpublished()->create();

        $this->assertSame([$published->id], Vehicle::published()->pluck('id')->all());
    }

    public function test_status_does_not_affect_publication(): void
    {
        $maintenance = Vehicle::factory()->status(VehicleStatus::Maintenance)->create();

        $this->assertTrue(Vehicle::published()->whereKey($maintenance->id)->exists());
        $this->assertSame(VehicleStatus::Maintenance, $maintenance->fresh()->status);
    }

    public function test_attributes_are_cast_to_enums(): void
    {
        $vehicle = Vehicle::factory()->create(['transmission' => 'automatic', 'status' => 'rented']);

        $this->assertSame(TransmissionType::Automatic, $vehicle->fresh()->transmission);
        $this->assertSame(VehicleStatus::Rented, $vehicle->fresh()->status);
    }

    public function test_slug_must_be_unique(): void
    {
        Vehicle::factory()->create(['slug' => 'avanza']);

        $this->expectException(QueryException::class);

        Vehicle::factory()->create(['slug' => 'avanza']);
    }

    public function test_a_vehicle_has_only_one_rate_per_service_type(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleRate::factory()->for($vehicle)->create(['service_type' => RentalServiceType::SelfDrive]);

        $this->expectException(QueryException::class);

        VehicleRate::factory()->for($vehicle)->create(['service_type' => RentalServiceType::SelfDrive]);
    }

    public function test_active_rates_exclude_inactive_ones(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleRate::factory()->for($vehicle)->create(['service_type' => RentalServiceType::SelfDrive]);
        VehicleRate::factory()->for($vehicle)->withDriver()->inactive()->create();

        $this->assertCount(2, $vehicle->rates);
        $this->assertCount(1, $vehicle->activeRates);
        $this->assertSame(RentalServiceType::SelfDrive, $vehicle->activeRates->first()->service_type);
    }

    public function test_price_is_stored_as_an_integer_number_of_rupiah(): void
    {
        $rate = VehicleRate::factory()->create(['price_per_day' => 350000]);

        $this->assertSame(350000, $rate->fresh()->price_per_day);
    }

    public function test_primary_image_relation_returns_the_primary_photo(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleImage::factory()->for($vehicle)->create(['sort_order' => 1]);
        $primary = VehicleImage::factory()->for($vehicle)->primary()->create(['sort_order' => 2]);

        $this->assertTrue($vehicle->primaryImage->is($primary));
        $this->assertCount(2, $vehicle->images);
    }

    public function test_images_are_returned_in_display_order(): void
    {
        $vehicle = Vehicle::factory()->create();
        $second = VehicleImage::factory()->for($vehicle)->create(['sort_order' => 2]);
        $first = VehicleImage::factory()->for($vehicle)->create(['sort_order' => 1]);

        $this->assertSame([$first->id, $second->id], $vehicle->images->pluck('id')->all());
    }

    public function test_force_deleting_a_vehicle_removes_its_images_and_rates(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleImage::factory()->for($vehicle)->create();
        VehicleRate::factory()->for($vehicle)->create();

        $vehicle->forceDelete();

        $this->assertDatabaseCount('vehicle_images', 0);
        $this->assertDatabaseCount('vehicle_rates', 0);
    }

    public function test_soft_deleted_vehicles_are_hidden_from_queries(): void
    {
        $vehicle = Vehicle::factory()->create();

        $vehicle->delete();

        $this->assertSame(0, Vehicle::published()->count());
        $this->assertSame(1, Vehicle::withTrashed()->count());
    }

    public function test_a_category_with_vehicles_cannot_be_deleted(): void
    {
        $category = VehicleCategory::factory()->create();
        Vehicle::factory()->for($category, 'category')->create();

        $this->expectException(QueryException::class);

        $category->delete();
    }
}
