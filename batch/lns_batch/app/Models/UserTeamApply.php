<?php

namespace App\Models;

class UserTeamApply extends BaseModel
{
    protected $table      = 'user_team_applys';  // テーブル名
    protected $guarded    = [];


    const TYPE_MEMBER           = 1;
    const TYPE_CONTACT          = 2;
    const TYPE_STAFF            = 3;

    const STATE_APPLY           = 1; // ユーザーがチームへ申請中
    const STATE_CANCEL          = 2; // ユーザーがチームへの申請を取り下げた
    const STATE_ACCEPT          = 3; // チームが申請を受諾した
    const STATE_DENY            = 4; // チームが申請を拒否した


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
