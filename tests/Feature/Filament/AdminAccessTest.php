<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_active_admin_can_open_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertOk();
    }

    public function test_inactive_admin_is_forbidden(): void
    {
        $this->actingAs(User::factory()->inactive()->create())->get('/admin')->assertForbidden();
    }

    public function test_all_resource_index_pages_render(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['users', 'vehicle-categories', 'vehicles', 'additional-fees'] as $path) {
            $this->get("/admin/{$path}")->assertOk();
            $this->get("/admin/{$path}/create")->assertOk();
        }
    }
}
