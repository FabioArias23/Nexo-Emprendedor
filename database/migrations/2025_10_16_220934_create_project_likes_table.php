<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_likes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            // Clave primaria compuesta para evitar likes duplicados
            $table->primary(['user_id', 'project_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_likes');
    }
};