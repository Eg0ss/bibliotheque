<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On ajoute 3 compteurs directement sur la table document_references.
        // Les stocker ici (plutôt que de faire un COUNT() à chaque requête)
        // rend l'affichage du catalogue rapide, même avec beaucoup de vues/likes.
        Schema::table('document_references', function (Blueprint $table) {
            $table->unsignedBigInteger('downloads_count')->default(0)->after('status'); // nb de téléchargements
            $table->unsignedBigInteger('likes_count')->default(0)->after('status');     // nb de likes
            $table->unsignedBigInteger('views_count')->default(0)->after('status');     // nb de vues
        });
    }

    public function down(): void
    {
        Schema::table('document_references', function (Blueprint $table) {
            $table->dropColumn(['downloads_count', 'likes_count', 'views_count']);
        });
    }
};