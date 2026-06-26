<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_references', function (Blueprint $table) {
            $table->id();

            // Métadonnées bibliographiques
            $table->string('title');                          // Titre du document
            $table->string('author');                         // Auteur(s)
            $table->string('publisher')->nullable();          // Éditeur / Institution
            $table->unsignedSmallInteger('publication_year')->nullable();    // Année de publication
            $table->string('language')->default('fr');        // Langue
            $table->string('isbn')->nullable()->unique();     // ISBN (optionnel)
            $table->text('abstract')->nullable();             // Résumé / description

            // Clés étrangères
            $table->foreignId('category_id')                 // Catégorie (Sciences, Droit...)
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignId('type_id')                     // Type (Thèse, Mémoire, Article...)
                ->nullable()
                ->constrained('types')
                ->nullOnDelete();

            $table->foreignId('submitted_by')                // User qui a soumis
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Statut dans le workflow de validation
            // pending → assigned → manager_approved → published / rejected
            $table->enum('status', [
                'pending',           // En attente d'assignation
                'assigned',          // Assigné à un gestionnaire
                'manager_approved',  // Validé par le gestionnaire
                'published',         // Publié dans le catalogue
                'rejected',          // Rejeté définitivement
            ])->default('pending');

            $table->string('cover_image')->nullable();        // Image de couverture

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_references');
    }
};