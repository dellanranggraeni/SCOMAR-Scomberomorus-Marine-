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
        Schema::create('catches', function (Blueprint $table) {
            $table->id();
            $table->string('year', 4);
            $table->string('season', 50);
            $table->string('period', 100);
            $table->decimal('total_catch', 12, 2)->default(0);
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('trips')->default(0);
            $table->decimal('cpue', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['year', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catches');
    }
};
