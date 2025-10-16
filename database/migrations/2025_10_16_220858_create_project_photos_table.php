<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('path', 2048);
            $table->string('caption')->nullable();
            $table->boolean('is_cover')->default(false); // Para marcar una foto como principal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_photos');
    }
};