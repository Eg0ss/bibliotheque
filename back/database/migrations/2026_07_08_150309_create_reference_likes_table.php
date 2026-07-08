<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cette table sert à savoir QUI a liké QUELLE référence.
        // C'est elle qui permet le "toggle" : si la ligne existe déjà,
        // on la supprime (unlike) ; sinon on la crée (like).
        Schema::create('reference_likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reference_id')
                ->constrained('document_references')
                ->cascadeOnDelete();

            $table->timestamps();

            // Contrainte d'unicité : un utilisateur ne peut avoir
            // qu'UNE seule ligne de like par référence (empêche les doublons).
            $table->unique(['user_id', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_likes');
    }
};