<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;

class MoonlightDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(MoonlightUserTableSeeder::class);
    }
}
