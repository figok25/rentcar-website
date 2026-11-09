<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_requirement_group_id')->constrained('rental_requirement_groups')->cascadeOnDelete();
            $table->text('requirement_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_requirements');
    }
};
