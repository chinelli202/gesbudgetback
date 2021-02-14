<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class LaratrustSetupOwnership extends Migration
{
    /**
     * Run the migrations.
     * Added by github.com/bloomverga
     *
     * @return  void
     */
    public function up()
    {
        // Create table for storing teams
        Schema::create('ownables', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableMorphs('ownable', 'ownable');
            $table->nullableMorphs('owner', 'owner');
            $table->string('description')->nullable();
            $table->timestamps();

            // Create a unique key
            $table->unique(['ownable_id', 'ownable_type', 'owner_id', 'owner_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('ownables');
    }
}
