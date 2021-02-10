<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class LaratrustSetupOwnership extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        // Create table for storing teams
        Schema::create('ownerships', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableMorphs('object', 'object');
            $table->nullableMorphs('owner', 'owner');
            $table->string('description')->nullable();
            $table->timestamps();

            // Create a unique key
            $table->unique(['object_id', 'object_type', 'owner_id', 'owner_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('ownerships');
    }
}
