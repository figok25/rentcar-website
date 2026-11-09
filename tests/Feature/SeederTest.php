<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\WebsiteSetting;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_core_data_without_demo_vehicles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::query()->count());
        $this->assertSame(
            ['mobil-kota', 'mobil-premium', 'minibus-hiace', 'motor'],
            VehicleCategory::query()->orderBy('sort_order')->pluck('slug')->all(),
        );
        $this->assertSame('Rental Kendaraan', WebsiteSetting::get('business_name'));
        $this->assertFalse(WebsiteSetting::bool('feature_motor_enabled'));

        // Lingkungan testing bukan local, jadi data contoh tidak ikut dibuat.
        $this->assertSame(0, Vehicle::query()->count());
    }

    public function test_requirements_start_empty_for_the_admin_to_fill_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('rental_requirement_groups', 0);
        $this->assertDatabaseCount('rental_requirements', 0);
    }

    public function test_demo_data_is_created_in_the_local_environment(): void
    {
        $this->app['env'] = 'local';

        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Vehicle::query()->count());
        $this->assertTrue(
            Vehicle::query()->whereHas('category', fn ($q) => $q->where('slug', VehicleCategory::MOTOR_SLUG))->exists(),
        );
    }

    public function test_admin_seeder_uses_the_credentials_from_the_environment(): void
    {
        $this->setEnv(['ADMIN_NAME' => 'Pemilik', 'ADMIN_EMAIL' => 'pemilik@example.test', 'ADMIN_PASSWORD' => 'rahasia-sekali-123']);

        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'pemilik@example.test')->firstOrFail();
        $this->assertSame('Pemilik', $admin->name);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('rahasia-sekali-123', $admin->password));
    }

    public function test_admin_seeder_does_not_overwrite_an_existing_account(): void
    {
        $this->setEnv(['ADMIN_NAME' => 'Pemilik', 'ADMIN_EMAIL' => 'pemilik@example.test', 'ADMIN_PASSWORD' => 'password-baru-456']);
        User::factory()->create(['email' => 'pemilik@example.test', 'password' => 'password-lama-123']);

        $this->seed(AdminUserSeeder::class);

        $this->assertSame(1, User::query()->count());
        $this->assertTrue(Hash::check('password-lama-123', User::query()->first()->password));
    }

    public function test_admin_seeder_generates_a_password_when_none_is_configured(): void
    {
        $this->setEnv(['ADMIN_NAME' => 'Pemilik', 'ADMIN_EMAIL' => 'pemilik@example.test', 'ADMIN_PASSWORD' => '']);

        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'pemilik@example.test')->firstOrFail();
        $this->assertNotEmpty($admin->password);
        $this->assertFalse(Hash::check('', $admin->password));
    }

    public function test_seeders_can_be_run_twice_without_duplicating_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::query()->count());
        $this->assertSame(4, VehicleCategory::query()->count());
        $this->assertSame(1, WebsiteSetting::query()->where('key', 'business_name')->count());
    }

    /**
     * @param  array<string, string>  $values
     */
    private function setEnv(array $values): void
    {
        foreach ($values as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    protected function tearDown(): void
    {
        foreach (['ADMIN_NAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD'] as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }

        parent::tearDown();
    }
}
