<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminFavoriteRubrics extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('admin_favorite_rubrics', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
            $table->string('name');
            $table->integer('order');
			$table->timestamps();
            $table->engine = 'InnoDB';
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('admin_favorite_rubrics');
	}

}
