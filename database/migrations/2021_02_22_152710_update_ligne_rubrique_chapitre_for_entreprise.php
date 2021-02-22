<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateLigneRubriqueChapitreForEntreprise extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('lignes', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code');

            // Create foreign keys
            $table->foreign('entreprise_code')->references('code')->on('entreprises')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('rubriques', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code');

            // Create foreign keys
            $table->foreign('entreprise_code')->references('code')->on('entreprises')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('chapitres', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code');

            // Create foreign keys
            $table->foreign('entreprise_code')->references('code')->on('entreprises')
                ->onUpdate('cascade')->onDelete('cascade');
        });
        
        Schema::table('titres', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code');

            // Create foreign keys
            $table->foreign('entreprise_code')->references('code')->on('entreprises')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropForeign(['entreprise_code']);
        }); 

        Schema::table('rubriques', function (Blueprint $table) {
            $table->dropForeign(['entreprise_code']);
        });

        Schema::table('chapitres', function (Blueprint $table) {
            $table->dropForeign(['entreprise_code']);
        });

        Schema::table('titres', function (Blueprint $table) {
            $table->dropForeign(['entreprise_code']);
        });
    }
}
