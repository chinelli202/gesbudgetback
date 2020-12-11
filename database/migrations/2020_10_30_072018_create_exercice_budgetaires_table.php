<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExerciceBudgetairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercices_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('label');
            $table->year('annee_vote')->unique();
            $table->date('date_vote')->unique();
            $table->date('date_debut')->unique();
            $table->date('date_cloture')->unique();
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercices_budgetaires');
    }
}
