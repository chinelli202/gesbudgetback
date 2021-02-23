<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChapitresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapitres', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('label');
            $table->string('description');
            $table->string('numero')->nullable();
            $table->enum('statut', ['draft', 'soumis','validé','rejetté','corrigé','supprimé', 'actif', 'archivé']);
            $table->enum('domaine', ['Fonctionnement','Mandat']);
            $table->enum('section', ['Dépenses','Recettes']);
            $table->string('sous_section')->nullable();
            $table->unsignedBigInteger('titre_id');
            $table->string('entreprise_code');
            $table->foreign('entreprise_code')->references('code')->on('entreprises')->onDelete('cascade');
            $table->foreign('titre_id')->references('id')->on('titres')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chapitres');
    }
}
