<?php

use Illuminate\Database\Seeder;

class LeaguesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // データいったん全削除
        DB::table('leagues')->truncate();
        // 初期データ
        DB::table('leagues')->insert([
            [
                'league_name'   => '会長',
                'league_tag'    => 'kaicho',
                'rank'          => 1,
                'point'         => 0,
            ],
            [
                'league_name'   => '社長',
                'league_tag'    => 'shacho',
                'rank'          => 1,
                'point'         => 0,
            ],
            [
                'league_name'   => '専務',
                'league_tag'    => 'senmu',
                'rank'          => 3,
                'point'         => 68,
            ],
            [
                'league_name'   => '本部長',
                'league_tag'    => 'honbucho',
                'rank'          => 4,
                'point'         => 60,
            ],
            [
                'league_name'   => '部長',
                'league_tag'    => 'bucho',
                'rank'          => 4,
                'point'         => 51,
            ],
            [
                'league_name'   => '課長',
                'league_tag'    => 'kacho',
                'rank'          => 5,
                'point'         => 42,
            ],
            [
                'league_name'   => '係長',
                'league_tag'    => 'kakaricho',
                'rank'          => 6,
                'point'         => 36,
            ],
            [
                'league_name'   => '室長',
                'league_tag'    => 'shitsucho',
                'rank'          => 7,
                'point'         => 27,
            ],
            [
                'league_name'   => '平社員',
                'league_tag'    => 'hira',
                'rank'          => 8,
                'point'         => 1,
            ],
        ]);

    }
}
