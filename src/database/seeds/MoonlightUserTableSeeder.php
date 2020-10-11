<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
			'super_user' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);

		echo 'Superuser login: '.$login.PHP_EOL;
		echo 'Superuser password: '.$password.PHP_EOL;
	}
}
