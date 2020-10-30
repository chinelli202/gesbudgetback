<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMockBudgetFonctionnementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mocks_budgets_fonctionnement', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->double('previsions');
            $table->double('realisations_mois');
            $table->double('realisations_precedentes');
            $table->double('realisations_cumulees');
            $table->double('engagements');
            $table->double('execution');
            $table->double('solde');
            $table->double('taux_execution');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mocks_budgets_fonctionnement');
    }
}
