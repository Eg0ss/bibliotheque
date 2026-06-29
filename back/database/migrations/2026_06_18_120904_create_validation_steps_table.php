<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_steps', function (Blueprint $table) {
            $table->id();

            // La demande de dépôt concernée
            $table->foreignId('depot_request_id')
                ->constrained('depot_requests')
                ->cascadeOnDelete();

            // Qui a pris cette décision (gestionnaire ou admin)
            $table->foreignId('performed_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Rôle de l'acteur au moment de la décision
            $table->enum('actor_role', ['gestionnaire', 'admin']);

            // La décision prise
            $table->enum('decision', [
                'manager_approved',  // Gestionnaire : accepte → soumet à l'admin
                'manager_rejected',  // Gestionnaire : rejette
                'published',         // Admin : publie définitivement
                'admin_rejected',    // Admin : rejette définitivement
                'resubmitted',       // Admin : renvoie au gestionnaire
            ]);

            // Commentaire / motif (obligatoire si rejet)
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_steps');
    }
};