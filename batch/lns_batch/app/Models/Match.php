<?php

namespace App\Models;

class Match extends BaseModel
{
    protected $table      = 'matches';  // テーブル名
    protected $guarded    = [];


    const MATCH_TYPE_ANY                    = 1;
    const MATCH_TYPE_LESS_SAME              = 2;
    const MATCH_TYPE_LESS_ONE_ON_THE_SAME   = 3;
    const MATCH_TYPE_LESS_TWO_ON_THE_SAME   = 4;
    
    const MATCH_STATE_DISABLED  = 0;
    const MATCH_STATE_RECRUIT   = 1;
    const MATCH_STATE_MATCHED   = 2;
    const MATCH_STATE_CANCEL    = 3;
    const MATCH_STATE_FINISHED  = 4;
    const MATCH_STATE_ABSTAINED = 5;
    const MATCH_STATE_ERROR     = 99;

    /**
     * scopeまわり
     */
    public function scopeHostOrApply( $query, $team_id )
    {
        return $query->where( function($q) use($team_id) {
                    $q->where('host_team_id', $team_id)
                      ->orWhere('apply_team_id', $team_id);
        });
    }
}
