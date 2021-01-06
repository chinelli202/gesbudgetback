<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRapportMensuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rapports_mensuels', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('mois');
            $table->unsignedBigInteger('ligne_id');
            $table->foreign('ligne_id')->references('id')->on('lignes')->onDelete('cascade');
            $table->integer('annee_exercice');
            $table->unsignedBigInteger('exercice_budgetaire_id');
            $table->foreign('exercice_budgetaire_id')->references('id')->on('exercices_budgetaires')->onDelete('cascade');
            $table->bigInteger('previsions');
            $table->bigInteger('realisations_mois');
            $table->bigInteger('realisations_precedentes');
            $table->bigInteger('realisations_cumulees');
            $table->bigInteger('engagements');
            $table->bigInteger('execution');
            $table->bigInteger('solde');
            $table->integer('taux_execution');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rapports_mensuels');
    }
}
