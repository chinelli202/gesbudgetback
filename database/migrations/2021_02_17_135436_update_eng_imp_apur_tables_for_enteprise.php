<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateEngImpApurTablesForEnteprise extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::table('engagements', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code')->nullable();

            // Create foreign keys
            $table->foreign('entreprise_code')->references('code')->on('entreprises')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('imputations', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code')->nullable();

            // Create foreign keys
            $table->foreign('entreprise_code')->references('code')->on('entreprises')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('apurements', function (Blueprint $table) {// Add team_id column
            $table->string('entreprise_code')->nullable();

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
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::table('permission_user', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::dropIfExists('teams');
    }
}
