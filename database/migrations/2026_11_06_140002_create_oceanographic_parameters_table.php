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
        Schema::create('oceanographic_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('year', 4);
            $table->decimal('temp', 6, 2)->comment('Suhu Permukaan Laut (SPL) dlm Celsius');
            $table->decimal('chlorophyll', 6, 2)->comment('Klorofil-a dlm mg/m3');
            $table->decimal('tss', 6, 2)->comment('Total Suspended Solids dlm mg/L');
            $table->decimal('current', 6, 2)->comment('Kecepatan arus dlm m/s');
            $table->string('status', 100)->default('Normal / Subur');
            $table->string('habitat_score', 100)->default('Optimal');
            $table->string('source', 255)->default('Pemantauan Terkini SCOMAR UPI');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oceanographic_parameters');
    }
};
