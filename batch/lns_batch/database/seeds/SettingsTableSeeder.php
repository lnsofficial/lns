<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // データいったん全削除
        DB::table('settings')->truncate();
        // 初期データ
        DB::table('settings')->insert([
            [
                'name'   => 'season_start',
                'value'  => '2018-10-09 00:00:00',
            ],
            [
                'name'   => 'season_end',
                'value'  => '2020-12-31 23:59:59',
            ],
            [
                'name'   => 'replacement_start',
                'value'  => '2018-05-01 00:00:00',
            ],
            [
                'name'   => 'replacement_end',
                'value'  => '2018-10-05 23:59:59',
            ],
        ]);

    }
}
