<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_logs', function (Blueprint $table) {
            // user_id est nullable car un visiteur NON connecté peut aussi
            // consulter une référence et faire incrémenter le compteur de vues.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reference_id')
                ->constrained('document_references')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultation_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['reference_id']);
            $table->dropColumn(['user_id', 'reference_id']);
        });
    }
};