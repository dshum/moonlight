<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminFavoritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_favorites', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('rubric_id')->unsigned()->index();
            $table->string('element_type');
            $table->integer('element_id');
            $table->integer('order');
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->unique(['user_id', 'rubric_id', 'element_type', 'element_id']);
            $table->index(['element_type', 'element_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_favorites');
    }
}
