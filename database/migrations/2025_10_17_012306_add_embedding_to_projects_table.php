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
        Schema::table('projects', function (Blueprint $table) {
            // Solo intenta crear la columna de vector si NO estamos ejecutando tests.
            if (!app()->runningUnitTests()) {
                // Dimensión a 768 para el modelo
                $table->vector('embedding', 768)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!app()->runningUnitTests()) {
                $table->dropColumn('embedding');
            }
        });
    }
};