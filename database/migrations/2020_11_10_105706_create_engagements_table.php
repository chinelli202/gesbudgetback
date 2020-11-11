<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class CreateEngagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->bigInteger('montant_ht')->nullable();
            $table->bigInteger('montant_ttc');
            $table->string('devise');

            $table->string('nature');
            $table->string('type');
            $table->string('etat');
            $table->string('statut');
            
            $table->integer('nb_imputations')->default(0);
            $table->bigInteger('cumul_imputations')->default(0);
            $table->integer('nb_apurements')->default(0);
            $table->bigInteger('cumul_apurements')->default(0);
            $table->string('saisisseur');
            $table->string('valideur_first')->nullable();
            $table->string('valideur_second')->nullable();
            $table->string('valideur_final')->nullable();
            $table->string('source');
            $table->unsignedBigInteger('ligne_id');
            
            $table->foreign('ligne_id')->references('id')->on('lignes')->onDelete('cascade');
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
        Schema::dropIfExists('engagements');
    }
}
