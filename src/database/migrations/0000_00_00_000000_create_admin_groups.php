<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminGroups extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_groups', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('default_permission')->nullable();
			$table->text('permissions')->nullable();
			$table->timestamps();
			$table->engine = 'InnoDB';
			$table->unique('name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('admin_groups');
	}

}
