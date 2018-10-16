<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table      = 'users';  // テーブル名
    protected $guarded    = [];

    private $rank = '';


    /**
     * リレーション周り
     */
    public function member()
    {
        return $this->hasOne( 'App\Models\TeamMember', 'user_id', 'id' );
    }
    public function ranks()
    {
        return $this->hasMany( 'App\Models\UserRank', 'user_id', 'id' );
    }


    /**
     * 画面表示まわり
     */
    public function viewSummonerName()
    {
        return str_limit($this->summoner_name, 16);
    }


    /**
     * // 現シーズン/前シーズンのうち高い方のランクのuser_rankレコードを返す
     *
     *
     */
    public function rank()
    {
        if( !empty($this->rank) )
        {
            return $this->rank;
        }

        $season_now    = LolSeason::findSeason();
        $season_before = LolSeason::findBeforeSeason();
        $user_rank_now    = '';
        $user_rank_before = '';

        foreach( $this->ranks as $rank )
        {
            if( $rank->season == $season_now->season )
            {
                $user_rank_now    = $rank;
            }
            else if( $rank->season == $season_before->season )
            {
                $user_rank_before = $rank;
            }
        }
        if( empty($user_rank_now) )
        {
            $user_rank_now    = new UserRank(['season'=>$season_now->season,    'user_id'=>$this->user_id,'tier'=>'UNRANK','rank'=>'UNRANK']);
        }
        if( empty($user_rank_before) )
        {
            $user_rank_before = new UserRank(['season'=>$season_before->season, 'user_id'=>$this->user_id,'tier'=>'UNRANK','rank'=>'UNRANK']);
        }


        if( $user_rank_now->tier == 'UNRANK' )
        {
            $this->rank = $user_rank_before;
        }
        else if( $user_rank_before->tier == 'UNRANK' )
        {
            $this->rank = $user_rank_now;
        }
        else
        {
            $point_now    = UserRank::RANK_LIST[$user_rank_now->tier][$user_rank_now->rank];
            $point_before = UserRank::RANK_LIST[$user_rank_before->tier][$user_rank_before->rank];

            $this->rank = ($point_now >= $point_before) ? $user_rank_now : $user_rank_before;
        }

        return $this->rank;
    }

}
