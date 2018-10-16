<?php

namespace App\Models;

class UserRank extends BaseModel
{
    protected $table      = 'user_ranks';  // テーブル名
    protected $guarded    = [];

    // ↓数値はなんでもいいので上下がわかるように。
    const RANK_LIST = [
        "CHALLENGER" => [ "I" => 100 ],
        "MASTER"     => [ "I" => 94  ],
        "DIAMOND"    => [ "I" => 88, "II" => 83, "III" => 78, "IV" => 73, "V" => 68],
        "PLATINUM"   => [ "I" => 63, "II" => 60, "III" => 57, "IV" => 54, "V" => 51],
        "GOLD"       => [ "I" => 48, "II" => 45, "III" => 42, "IV" => 39, "V" => 36],
        "SILVER"     => [ "I" => 33, "II" => 31, "III" => 29, "IV" => 27, "V" => 25],
        "BRONZE"     => [ "I" => 23, "II" => 22, "III" => 21, "IV" => 20, "V" => 10],
        "UNRANK"     => [ "UNRANK" => 0],
    ];

    const RANK_COLOR_CLASS = [
        'background-color' => [
            "CHALLENGER"    => '#e0ffff',  // lightcyan
            "MASTER"        => '#e0ffff',  // lightcyan
            "DIAMOND"       => '#00bfff',  // deepskyblue
            "PLATINUM"      => '#32cd32',  // limegreen
            "GOLD"          => '#ffd700',  // gold
            "SILVER"        => '#c0c0c0',  // silver
            "BRONZE"        => '#b8860b',  // darkgoldenrod
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
