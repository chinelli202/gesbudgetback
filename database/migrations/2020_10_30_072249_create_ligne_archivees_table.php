<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLigneArchiveesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lignes_archivees', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('label');
            $table->string('description',1000);
            $table->double('montant');
            $table->enum('domaine', ['Fonctionnement','Mandat']);
            $table->enum('section', ['Dépenses','Recettes']);
            $table->year('annee');
            $table->string('rubrique');
            $table->string('description_rubrique');
            $table->string('chapitre');
            $table->string('description_chapitre');
            $table->string('titre');
            $table->string('description_titre');
            $table->unsignedBigInteger('exercice_budgetaire_id');
            $table->foreign('exercice_budgetaire_id')->references('id')->on('exercices_budgetaires');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lignes_archivees');
    }
}
