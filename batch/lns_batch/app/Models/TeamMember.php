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
        return $this->belongsTo('Team', 'team_id', 'id');
    }

}
