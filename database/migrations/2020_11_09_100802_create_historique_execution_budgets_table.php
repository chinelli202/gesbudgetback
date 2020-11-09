<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriqueExecutionBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historique_execution_budgets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('editeur');
            $table->string('type_edition'); /** VALIDP, VALIDS, VALIDF, UPDATE, RESEND, CLOSE, RESTORE
                * SEND_BACK, ADD_COMMENT, CANCEL_VALIDATION, IMPUTER, APURER */
            $table->string('id_objet_edite');
            $table->string('type_objet_edite'); // Nom de la table où se trouve l'objet édité
            $table->string('commentaire')->nullable();

            $table->foreign('editeur')->references('matricule')->on('users')
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
        Schema::dropIfExists('historique_execution_budgets');
    }
}
