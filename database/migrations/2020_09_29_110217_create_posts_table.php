<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string("post_title")->nullable();
            $table->string("slug")->nullable();
            $table->string("category")->nullable();
            $table->string("author")->nullable();
            $table->tinyInteger("status")->nullable()->default(0);
            $table->tinyInteger("published")->nullable()->default(0);
            $table->tinyInteger("draft")->nullable()->default(0);
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
        Schema::dropIfExists('posts');
    }
}
