<?php

namespace App\Models;

class TeamJoin extends BaseModel
{
    protected $table      = 'team_joins';  // テーブル名
    protected $guarded    = [];


    const STATE_CANCEL = 0;
    const STATE_ENABLE = 1;


    /**
     * リレーション周り
     */
    public function team()
    {
        return $this->belongsTo( 'App\Models\Team', 'team_id', 'id' );
    }
    public function match()
    {
        return $this->belongsTo( 'App\Models\Match', 'match_id', 'id' );
    }

}
