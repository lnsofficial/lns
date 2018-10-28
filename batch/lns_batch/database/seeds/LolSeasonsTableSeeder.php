<?php

use Illuminate\Database\Seeder;

class LolSeasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // データいったん全削除
        DB::table('lol_seasons')->truncate();
        // 初期データ
        DB::table('lol_seasons')->insert([
            [
                'season'   => 'S1',
                'start_at' => '2010-07-13 05:00:00',
                'end_at'   => '2011-11-29 04:59:59',
            ],
            [
                'season'   => 'S2',
                'start_at' => '2011-11-29 05:00:00',
                'end_at'   => '2013-02-01 04:59:59',
            ],
            [
                'season'   => 'S3',
                'start_at' => '2013-02-01 05:00:00',
                'end_at'   => '2014-01-15 04:59:59',
            ],
            [
                'season'   => 'S4',
                'start_at' => '2014-01-15 05:00:00',
                'end_at'   => '2015-01-21 04:59:59',
            ],
            [
                'season'   => 'S5',
                'start_at' => '2015-01-21 05:00:00',
                'end_at'   => '2016-01-20 04:59:59',
            ],
            [
                'season'   => 'S6',
                'start_at' => '2016-01-20 05:00:00',
                'end_at'   => '2016-12-07 04:59:59',
            ],
            [
                'season'   => 'S7',
                'start_at' => '2016-12-07 05:00:00',
                'end_at'   => '2018-01-16 04:59:59',
            ],
            [
                'season'   => 'S8',
                'start_at' => '2018-01-16 05:00:00',
                'end_at'   => '2020-12-31 04:59:59',
            ],
        ]);

    }
}
