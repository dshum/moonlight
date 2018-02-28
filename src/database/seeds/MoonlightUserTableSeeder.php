<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Moonlight\Models\User;
use Moonlight\Models\Group;
use Carbon\Carbon;

class MoonlightUserTableSeeder extends Seeder {

	public function run()
	{
		$login = 'magus';
		$password = Str::random(8);

		DB::table('admin_users')->insert([
			'login' => $login,
			'password' => password_hash($password, PASSWORD_DEFAULT),
			'email' => 'denis-shumeev@yandex.ru',
			'first_name' => 'Super',
			'last_name' => 'Magus',
			'superuser' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);

		echo 'Superuser login: '.$login.PHP_EOL;
		echo 'Superuser password: '.$password.PHP_EOL;
	}
}