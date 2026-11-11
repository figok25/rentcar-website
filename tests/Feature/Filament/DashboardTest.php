<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_has_no_filament_info_card(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertOk()
            ->assertSee('fi-account-widget', false)
            ->assertDontSee('fi-filament-info-widget', false)
            ->assertDontSee('Dokumentasi')
            ->assertDontSee('Documentation')
            ->assertDontSee('GitHub');
    }

    public function test_filament_info_widget_is_not_registered(): void
    {
        $this->assertNotContains(FilamentInfoWidget::class, Filament::getPanel('admin')->getWidgets());
    }
}
