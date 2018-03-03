<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminUsersGroupsPivot extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_users_groups_pivot', function(Blueprint $table) {
			$table->integer('user_id')->unsigned()->index();
			$table->integer('group_id')->unsigned()->index();
			$table->engine = 'InnoDB';
			$table->primary(array('user_id', 'group_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('admin_users_groups_pivot');
	}

}
