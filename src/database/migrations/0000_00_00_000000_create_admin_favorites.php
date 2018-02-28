<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminFavorites extends Migration {

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
			$table->string('class_id');
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
		Schema::dropIfExists('admin_favorites');
	}

}
