<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(VideoFormatsSeeder::class);
        $this->call(OutputGroupsSeeder::class);
        $this->call(OutputsSeeder::class);
    }
}
