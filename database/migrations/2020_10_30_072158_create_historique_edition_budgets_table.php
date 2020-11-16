<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriqueEditionBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historiques_editions_budgets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('id_objet_edite');
            $table->string('type_objet_edite'); // ligne, rubrique, chapitre
            $table->string('type_edition'); // rejet, correction, modification, soumission, validation, suppression
            $table->string('commentaire')->nullable();
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
        Schema::dropIfExists('historiques_editions_budgets');
    }
}
