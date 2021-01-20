<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('sexe');
            $table->date('date_naissance')->nullable();
            $table->date('date_embauche')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('addresse')->nullable();
            $table->string('num_compte')->nullable()->unique();
            $table->string('dom_bancaire')->nullable(); //domiciliation bancaire
            $table->string('representation');
            $table->string('email')->unique();
            $table->string('matricule')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('division')->nullable();
            $table->string('fonction')->nullable();
            $table->string('saisisseur');
            $table->string('valideur')->nullable();
            $table->string('source')->nullable();
            $table->string('statut_utilisateur')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
