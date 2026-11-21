<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fishing_gears', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('category', 100)->default('Ramah Lingkungan'); // Ramah Lingkungan, Selektif, Tradisional, dll
            $table->string('target_species', 150)->nullable();
            $table->string('selectivity_level', 50)->default('Tinggi'); // Tinggi, Sedang, Rendah
            $table->string('status', 50)->default('Direkomendasikan'); // Direkomendasikan, Diizinkan, Dibatasi, Dilarang
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_gears');
    }
};
