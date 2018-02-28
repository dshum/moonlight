<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

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
