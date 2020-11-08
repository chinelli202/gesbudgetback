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
            /** 
             * next_statut: Empty/null/undefined by default
             *   When empty, the engagement will follow the normal workflow of statut evolution
             *   INIT -> VALIDP -> VALIDP -> VALIDS -> VALIDF.
             *   Elsewhere, the value will be an indicator of the next action to perform on the engagement.
             *   We'll use it espacially to for the 'Renvoyer l'engagement' feature.
             *   Ex: The operator n create the engagement X (statut = INIT, next_statut is empty).
             *       The operator n+1 review engagement X and decide to send back to operator n.
             *       The engagement X return back to X with (statut = INIT, next_statut = INIT)
             *       meaning that the engagement should be 'INIT' again.
             *       Once the operator re-INIT the engagement, it'll be updated to
             *       (statut = INIT, next_statut to empty)
             * */
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
            $table->string('next_statut')->nullable();
            
            $table->integer('nb_imputations')->default(0);
            $table->bigInteger('cumul_imputations')->default(0);
            $table->integer('nb_apurements')->default(0);
            $table->bigInteger('cumul_apurements')->default(0);
            $table->string('saisisseur');
            $table->string('valideur_first')->nullable();
            $table->string('valideur_second')->nullable();
            $table->string('valideur_final')->nullable();
            $table->string('source');
            

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
