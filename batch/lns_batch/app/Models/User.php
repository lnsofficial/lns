<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table      = 'users';  // テーブル名
    protected $guarded    = [];


    /**
     * リレーション周り
     */
    public function member()
    {
        return $this->hasOne( 'App\Models\TeamMember', 'user_id', 'id' );
    }

}
