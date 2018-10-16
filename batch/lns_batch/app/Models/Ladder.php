<?php

namespace App\Models;

class Ladder extends BaseModel
{
    protected $table      = 'ladders';  // テーブル名
    protected $guarded    = [];

    const SEASON_NOW    = 'S5';
    const SEASON_BEFORE = 'S4';

    /**
     * リレーション周り
     */
    public function league()
    {
        return $this->belongsTo( 'App\Models\League', 'league_id', 'id' );
    }
    public function team()
    {
        return $this->belongsTo( 'App\Models\Team', 'team_id', 'id' );
    }

}
