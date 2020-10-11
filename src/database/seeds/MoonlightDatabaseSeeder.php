<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MoonlightDatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MoonlightUserTableSeeder::class);
    }
}
