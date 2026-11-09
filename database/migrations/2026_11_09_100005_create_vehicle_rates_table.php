<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            // Rupiah utuh, bukan string berformat. Format tampilan dilakukan di layer view.
            $table->unsignedBigInteger('price_per_day');
            $table->boolean('includes_driver')->default(false);
            $table->boolean('includes_fuel')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Satu harga per jenis layanan untuk setiap kendaraan.
            $table->unique(['vehicle_id', 'service_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_rates');
    }
};
