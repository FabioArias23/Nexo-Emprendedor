<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // El estado del ciclo de vida del proyecto
            $table->enum('status', ['active', 'funded', 'expired'])->default('active')->after('market_potential');
            // La fecha límite para alcanzar la meta de financiación
            $table->timestamp('deadline')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['status', 'deadline']);
        });
    }
};