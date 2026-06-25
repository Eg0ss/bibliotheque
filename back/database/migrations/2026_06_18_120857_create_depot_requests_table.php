<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depot_requests', function (Blueprint $table) {
            $table->id();

            // Le user qui a fait la demande
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // La référence documentaire créée avec la demande
            $table->foreignId('reference_id')
                ->constrained('document_references')
                ->cascadeOnDelete();

            // Statut de la demande (suit le workflow)
            $table->enum('status', [
                'pending',           // Soumise, en attente
                'assigned',          // Assignée à un gestionnaire
                'manager_approved',  // Gestionnaire a validé
                'published',         // Admin a publié
                'rejected',          // Rejetée
            ])->default('pending');

            // Justification en cas de rejet ou de retour
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depot_requests');
    }
};