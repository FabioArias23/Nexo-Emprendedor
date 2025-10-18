<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('investor_id')->constrained('users')->onDelete('cascade'); // El inversor interesado
            
            $table->enum('status', [
                'pending',      // Interés inicial mostrado por el inversor
                'negotiating',  // Emprendedor e inversor están en contacto
                'accepted',     // El emprendedor acepta la negociación
                'closed',       // Acuerdo cerrado y financiado
                'rejected'      // El emprendedor o inversor rechaza la propuesta
            ])->default('pending');

            $table->decimal('proposed_amount', 15, 2); // Monto que el inversor ofrece
            $table->text('message')->nullable(); // Mensaje inicial del inversor
            
            // Campos para el seguimiento una vez cerrado
            $table->decimal('final_amount', 15, 2)->nullable();
            $table->text('agreement_details')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};