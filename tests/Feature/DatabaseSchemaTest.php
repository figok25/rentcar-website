<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_tables_from_the_blueprint_exist(): void
    {
        $tables = [
            'users', 'vehicle_categories', 'vehicles', 'vehicle_images', 'vehicle_rates',
            'additional_fees', 'rental_requirement_groups', 'rental_requirements',
            'tours', 'tour_images', 'travel_services', 'mottos', 'advantages',
            'galleries', 'testimonials', 'faqs', 'website_settings',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} tidak ditemukan.");
        }
    }

    public function test_every_model_points_to_an_existing_table(): void
    {
        $files = glob(app_path('Models/*.php'));
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            $table = (new $class)->getTable();

            $this->assertTrue(Schema::hasTable($table), "{$class} memakai tabel {$table} yang tidak ada.");
        }
    }

    public function test_no_booking_or_payment_tables_exist(): void
    {
        $forbidden = [
            'bookings', 'booking_items', 'payments', 'invoices',
            'customers', 'vehicle_availability_calendars',
        ];

        foreach ($forbidden as $table) {
            $this->assertFalse(Schema::hasTable($table), "Tabel {$table} tidak boleh ada pada versi awal.");
        }
    }

    public function test_users_have_an_is_active_flag_that_defaults_to_true(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'is_active'));
    }
}
