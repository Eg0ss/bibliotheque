<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Référence documentaire à laquelle ce fichier est rattaché
            $table->foreignId('reference_id')
                ->constrained('document_references')
                ->cascadeOnDelete();

            $table->string('file_path');              // Chemin du fichier sur le serveur
            $table->string('original_name');          // Nom original du fichier uploadé
            $table->string('mime_type')->default('application/pdf');
            $table->unsignedBigInteger('file_size')->nullable(); // Taille en octets
            $table->unsignedTinyInteger('version')->default(1);  // Version du fichier

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};