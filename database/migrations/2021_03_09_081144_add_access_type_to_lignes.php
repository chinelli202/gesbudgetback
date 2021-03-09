<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessTypeToLignes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->string('access_type');
        });

        Schema::table('titres', function (Blueprint $table) {
            $table->string('extended_ligne')->nullable();
        });

        Schema::table('chapitres', function (Blueprint $table) {
            $table->string('extended_ligne')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });

        Schema::table('titres', function (Blueprint $table) {
            $table->dropColumn('extended_ligne');
        });

        Schema::table('titres', function (Blueprint $table) {
            $table->dropColumn('extended_ligne');
        });
    }
}
