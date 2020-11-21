<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apurements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('engagement_id');  //creates confusion. rename to code_engagement. 
            $table->string('libelle');
            $table->string('reference_paiement');
            $table->bigInteger('montant_ht')->nullable();
            $table->bigInteger('montant_ttc');
            $table->string('devise');
            $table->string('observations');

            $table->string('statut');
            $table->string('next_statut')->nullable();
            $table->json('documents')->nullable();

            $table->string('saisisseur');
            $table->string('valideur_first')->nullable();
            $table->string('valideur_second')->nullable();
            $table->string('valideur_final')->nullable();
            $table->string('source');
            
            $table->foreign('engagement_id')->references('code')->on('engagements')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('saisisseur')->references('matricule')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('valideur_first')->references('matricule')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('valideur_second')->references('matricule')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('valideur_final')->references('matricule')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apurements');
    }
}
