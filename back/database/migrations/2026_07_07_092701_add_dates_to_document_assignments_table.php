<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter les nouvelles colonnes.
     */
    public function up(): void
    {
        Schema::table('document_assignments', function (Blueprint $table) {

            /*
             |------------------------------------------------------------
             | Date réelle d'assignation
             |------------------------------------------------------------
             | Elle sera remplie automatiquement dans le Controller
             | avec now().
             */
            $table->timestamp('assigned_at')
                  ->nullable()
                  ->after('assigned_by');

            /*
             |------------------------------------------------------------
             | Date limite de traitement
             |------------------------------------------------------------
             | Elle sera choisie par l'administrateur lors de
             | l'assignation.
             */
            $table->date('due_date')
                  ->nullable()
                  ->after('assigned_at');

        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::table('document_assignments', function (Blueprint $table) {

            $table->dropColumn([
                'assigned_at',
                'due_date',
            ]);

        });
    }
};