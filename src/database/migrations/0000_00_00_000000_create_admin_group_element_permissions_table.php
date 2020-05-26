<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminGroupElementPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_group_element_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned()->index();
            $table->string('element_type');
            $table->integer('element_id');
            $table->string('permission');
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->unique(['group_id', 'element_type', 'element_id']);
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
        Schema::dropIfExists('admin_group_element_permissions');
    }
}
