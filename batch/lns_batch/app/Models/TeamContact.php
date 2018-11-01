<?php

namespace App\Models;

class TeamContact extends BaseModel
{
    protected $table      = 'teams_contact';  // テーブル名
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
