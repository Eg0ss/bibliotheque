<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_assignments', function (Blueprint $table) {
            $table->id();

            // La demande de dépôt assignée
            $table->foreignId('depot_request_id')
                  ->constrained('depot_requests')
                  ->cascadeOnDelete();

            // L'admin qui assigne
            $table->foreignId('assigned_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Le gestionnaire destinataire
            $table->foreignId('assigned_to')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Instructions optionnelles de l'admin au gestionnaire
            $table->text('instructions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_assignments');
    }
};