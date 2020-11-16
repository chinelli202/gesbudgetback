<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLignesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lignes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('label');
            $table->string('description',1000);
            $table->double('montant');
            $table->enum('domaine', ['Fonctionnement','Mandat']);
            $table->enum('section', ['Dépenses','Recettes']);
            $table->string('sous_section')->nullable();
            $table->enum('statut', ['draft', 'soumis','validé','rejetté','corrigé','supprimé']);
            $table->unsignedBigInteger('rubrique_id');
            $table->foreign('rubrique_id')->references('id')->on('rubriques')->onDelete('cascade');
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
        Schema::dropIfExists('lignes');
    }
}
