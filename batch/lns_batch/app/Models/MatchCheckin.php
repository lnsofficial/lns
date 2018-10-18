<?php

namespace App\Models;

class MatchCheckin extends BaseModel
{
    protected $table      = 'match_checkins';  // テーブル名
    protected $guarded    = [];


    /**
     * リレーション周り
     */
    public function match()
    {
        return $this->belongsTo( 'App\Models\Match', 'match_id', 'id' );
    }
    public function team()
    {
        return $this->belongsTo( 'App\Models\Team', 'team_id', 'id' );
    }
    public function user()
    {
        return $this->belongsTo( 'App\Models\User', 'user_id', 'id' );
    }

}
