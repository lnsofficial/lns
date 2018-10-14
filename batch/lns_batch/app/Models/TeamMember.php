<?php

namespace App\Models;

class TeamMember extends BaseModel
{
    protected $table      = 'team_members';  // テーブル名
    protected $guarded    = [];


    /**
     * リレーション周り
     */
    public function team()
    {
        return $this->belongsTo( 'App\Models\Team', 'team_id', 'id' );
    }
    public function user()
    {
        return $this->belongsTo( 'App\Models\User', 'user_id', 'id' );
    }

}
