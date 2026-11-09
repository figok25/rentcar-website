<?php

namespace Tests\Unit;

use App\Enums\FuelType;
use App\Enums\RentalServiceType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_vehicle_status_values_match_the_blueprint(): void
    {
        $this->assertSame(
            ['available', 'rented', 'maintenance', 'unavailable'],
            array_column(VehicleStatus::cases(), 'value'),
        );
    }

    public function test_vehicle_status_has_indonesian_labels(): void
    {
        $this->assertSame('Tersedia', VehicleStatus::Available->label());
        $this->assertSame('Sedang disewa', VehicleStatus::Rented->label());
        $this->assertSame('Maintenance', VehicleStatus::Maintenance->label());
        $this->assertSame('Tidak tersedia', VehicleStatus::Unavailable->label());
    }

    public function test_every_status_has_a_label_and_a_color(): void
    {
        foreach (VehicleStatus::cases() as $status) {
            $this->assertNotSame('', $status->label());
            $this->assertNotSame('', $status->color());
        }
    }

    public function test_options_map_value_to_label(): void
    {
        $this->assertSame('Lepas kunci', RentalServiceType::options()['self_drive']);
        $this->assertSame('Dengan driver', RentalServiceType::options()['with_driver']);
        $this->assertSame('Matic', TransmissionType::options()['automatic']);
        $this->assertSame('Bensin', FuelType::options()['gasoline']);
        $this->assertCount(4, VehicleStatus::options());
    }
}
