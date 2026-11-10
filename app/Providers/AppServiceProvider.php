<?php

namespace App\Providers;

use App\Enums\CatalogKind;
use App\Models\WebsiteSetting;
use App\Services\VehicleCatalog;
use App\Services\WhatsAppLink;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Data situs untuk layout publik (nama bisnis, kontak, menu opsional).
        // Dihitung sekali per request lewat binding scoped.
        $this->app->scoped('public.site', fn (): array => [
            'name' => WebsiteSetting::get('business_name', config('app.name')),
            'logo' => WebsiteSetting::get('logo_path'),
            'phone' => WebsiteSetting::get('phone_number'),
            'address' => WebsiteSetting::get('address'),
            'instagram' => WebsiteSetting::get('instagram_url'),
            'facebook' => WebsiteSetting::get('facebook_url'),
            'footerText' => WebsiteSetting::get('footer_description'),
            'whatsapp' => WhatsAppLink::url(),
            'showMotor' => VehicleCatalog::motorEnabled()
                && (new VehicleCatalog)->visible(CatalogKind::Motor)->exists(),
        ]);

        RateLimiter::for('inquiry', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));

        View::composer(['layouts.public', 'pages.*'], function ($view): void {
            $view->with('site', $this->app->make('public.site'));
        });
    }
}
