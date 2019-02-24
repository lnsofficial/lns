<?php

namespace App\Models;

class UserRank extends BaseModel
{
    protected $table      = 'user_ranks';  // テーブル名
    protected $guarded    = [];

    // ↓数値はなんでもいいので上下がわかるように。
    const RANK_LIST = [
        "CHALLENGER" => [ "I" => 100 ],
        "GRANDMASTER"=> [ "I" => 92  ],
        "MASTER"     => [ "I" => 85  ],
        "DIAMOND"    => [ "I" => 79, "II" => 74, "III" => 69, "IV" => 64, "V" => 68],
        "PLATINUM"   => [ "I" => 59, "II" => 55, "III" => 51, "IV" => 47, "V" => 51],
        "GOLD"       => [ "I" => 43, "II" => 40, "III" => 37, "IV" => 34, "V" => 36],
        "SILVER"     => [ "I" => 31, "II" => 29, "III" => 27, "IV" => 25, "V" => 25],
        "BRONZE"     => [ "I" => 23, "II" => 22, "III" => 21, "IV" => 20, "V" => 10],
        "IRON"       => [ "I" => 19, "II" => 18, "III" => 17, "IV" => 10],
        "UNRANK"     => [ "UNRANK" => 0],
    ];

    const RANK_COLOR_CLASS = [
        'background-color' => [
            "CHALLENGER"    => '#e0ffff',  // lightcyan
            "GRANDMASTER"   => '#e0ffff',  // lightcyan
            "MASTER"        => '#e0ffff',  // lightcyan
            "DIAMOND"       => '#00bfff',  // deepskyblue
            "PLATINUM"      => '#32cd32',  // limegreen
            "GOLD"          => '#ffd700',  // gold
            "SILVER"        => '#c0c0c0',  // silver
            "BRONZE"        => '#b8860b',  // darkgoldenrod
            "IRON"          => '#b8860b',  // darkgoldenrod
            "UNRANK"        => '#ffffff',  // white
        ],
    ];


    /**
     * 画面表示まわり
     */
    public function viewTierRank()
    {
        $label = $this->tier;
        if( $this->rank != 'UNRANK' )
        {
            $label .= ' ' . $this->rank;
        }
        return $label;
    }


    public static function point2rank( $needle )
    {
        $rank = [
            'tier' => 'UNRANK',
            'rank' => 'UNRANK',
        ];
        foreach( self::RANK_LIST as $tier=>$divisions )
        {
            foreach( $divisions as $division=>$point )
            {
                if( $needle >= $point)
                {
                    $rank['tier'] = $tier;
                    $rank['rank'] = $division;
                    break 2;
                }
            }
        }
        return $rank;
    }

    /**
     * 現シーズンのレコードを取得
     *
     *
     */
    public static function findByUserId( $user_id )
    {
        $season = LolSeason::findSeason();
        return self::firstOrNew(['season'=>$season->season, 'user_id'=>$user_id]);
    }

    /**
     * 現在の1個前のシーズンのレコードを取得
     *
     *
     */
    public static function findBeforeSeasonByUserId( $user_id )
    {
        $season = LolSeason::findBeforeSeason();
        if( !empty($season) )
        {
            return self::firstOrNew(['season'=>$season->season, 'user_id'=>$user_id]);
        }
        else
        {
            \Log::error('前シーズンレコードが取れないので現シーズンで');
            return self::findByUserId( $user_id );
        }
    }

}
