<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La table existait déjà mais était vide (seulement id + timestamps).
        // On ajoute qui a téléchargé et quelle référence.
        // Pas de contrainte "unique" ici : un même utilisateur peut télécharger
        // plusieurs fois, chaque téléchargement doit être compté (demande du client).
        Schema::table('download_logs', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reference_id')
                ->constrained('document_references')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('download_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['reference_id']);
            $table->dropColumn(['user_id', 'reference_id']);
        });
    }
};