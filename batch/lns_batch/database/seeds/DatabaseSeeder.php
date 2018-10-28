<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(LolSeasonsTableSeeder::class);
        $this->call(LeaguesTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
    }
}
