<?php

namespace App\Models;

class League extends BaseModel
{
    protected $table      = 'leagues';  // テーブル名
    protected $guarded    = [];


    /**
     * リレーション周り
     */
    public function ladders()
    {
        return $this->hasMany( 'App\Models\Ladder', 'league_id', 'id' );
    }

}
