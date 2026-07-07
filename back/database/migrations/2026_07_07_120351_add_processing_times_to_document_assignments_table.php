<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter les colonnes de mesure du temps de traitement réel.
     *
     * On utilise Schema::table() et non Schema::create()
     * car la table document_assignments existe déjà.
     */
    public function up(): void
    {
        Schema::table('document_assignments', function (Blueprint $table) {

            /*
             |----------------------------------------------------------
             | processing_started_at
             |----------------------------------------------------------
             | Enregistrée UNE SEULE FOIS : lors du premier clic
             | du gestionnaire sur "Voir" (méthode show() du
             | GestionnaireController).
             |
             | nullable() : au moment de l'assignation, le gestionnaire
             | n'a pas encore ouvert le document — la valeur est NULL
             | jusqu'au premier clic.
             |
             | after('due_date') : on place la colonne juste après
             | due_date pour garder un ordre logique dans la table.
             */
            $table->timestamp('processing_started_at')
                  ->nullable()
                  ->after('due_date');

            /*
             |----------------------------------------------------------
             | processed_at
             |----------------------------------------------------------
             | Enregistrée lors du clic sur "Accepter" ou "Rejeter"
             | (méthode decide() du GestionnaireController).
             |
             | nullable() : tant que le gestionnaire n'a pas pris
             | de décision, la valeur est NULL.
             |
             | after('processing_started_at') : ordre logique
             | début → fin.
             */
            $table->timestamp('processed_at')
                  ->nullable()
                  ->after('processing_started_at');

        });
    }

    /**
     * Annuler la migration — supprime les deux colonnes ajoutées.
     *
     * dropColumn() accepte un tableau : les deux colonnes
     * sont supprimées en une seule instruction.
     */
    public function down(): void
    {
        Schema::table('document_assignments', function (Blueprint $table) {

            $table->dropColumn([
                'processing_started_at',
                'processed_at',
            ]);

        });
    }
};