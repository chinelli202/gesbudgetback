<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRubriquesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rubriques', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('numero');
            $table->string('label');
            $table->string('description');
            $table->enum('statut', ['draft', 'soumis','validé']);
            $table->enum('domaine', ['Fonctionnement','Mandat']);
            $table->enum('section', ['Dépenses','Recettes']);
            $table->string('sous_section')->nullable();
            $table->unsignedBigInteger('chapitre_id');
            $table->foreign('chapitre_id')->references('id')->on('chapitres')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rubriques');
    }
}
