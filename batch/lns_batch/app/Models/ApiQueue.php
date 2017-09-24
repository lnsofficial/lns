<?php

namespace App\Models;

class ApiQueue extends BaseModel
{
    protected $table      = 'api_queues';  // テーブル名
    protected $guarded    = [];

    const ACTION_UPDATE_SUMMONER        = 1;  // サモナーネーム変更時のriotApiへの問い合わせ
    const ACTION_REGISTER_SUMMONER      = 2;  // ユーザー初回登録時のriotApiへの問い合わせ

    const STATE_UNTREATED               = 0;
    const STATE_DOING                   = 1;
    const STATE_FAILED                  = 2;
    const STATE_FINISHED                = 3;

}
