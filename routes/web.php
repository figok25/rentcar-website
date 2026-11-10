<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

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
