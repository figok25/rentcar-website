<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\VehicleController;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

Route::middleware(SecurityHeaders::class)->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/rental-mobil', [VehicleController::class, 'index'])
        ->defaults('kind', 'mobil')->name('vehicles.index');
    Route::get('/rental-mobil/{slug}', [VehicleController::class, 'show'])
        ->defaults('kind', 'mobil')->name('vehicles.show');

    // Motor = kategori "Motor" di tabel vehicles; halaman aktif jika fitur dinyalakan admin.
    Route::get('/rental-motor', [VehicleController::class, 'index'])
        ->defaults('kind', 'motor')->name('motor.index');
    Route::get('/rental-motor/{slug}', [VehicleController::class, 'show'])
        ->defaults('kind', 'motor')->name('motor.show');

    Route::get('/paket-wisata', [TourController::class, 'index'])->name('tours.index');
    Route::get('/paket-wisata/{slug}', [TourController::class, 'show'])->name('tours.show');
    Route::get('/travel', [TravelController::class, 'index'])->name('travel.index');
    Route::get('/travel/{slug}', [TravelController::class, 'show'])->name('travel.show');

    Route::get('/syarat-sewa', [PageController::class, 'requirements'])->name('requirements');
    Route::get('/kontak', [PageController::class, 'contact'])->name('contact');

    // Rencana sewa -> pesan WhatsApp (tanpa booking, tanpa penyimpanan data).
    Route::post('/tanya-via-whatsapp', [InquiryController::class, 'store'])
        ->middleware('throttle:inquiry')->name('inquiry.store');

    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
});
