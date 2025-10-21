<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // A qué usuario pertenece esta notificación
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // El texto que se mostrará
            $table->string('message');
            // El enlace al que se redirigirá al hacer clic (puede ser null)
            $table->string('link')->nullable();
            // Para saber si la notificación ya fue leída
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};