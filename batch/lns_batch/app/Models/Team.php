<?php

namespace App\Models;

class Team extends BaseModel
{
    protected $table      = 'teams';  // テーブル名
    protected $guarded    = [];

    const LOGO_STATUS_UNREGISTERED      = 0; // 未登録
    const LOGO_STATUS_UNAUTHENTICATED   = 1; // アップロードしたけどまだ運営で未認証
    const LOGO_STATUS_AUTHENTICATED     = 2; // 運営にて認証済み
    const LOGO_STATUS_AUTHENTICATEERROR = 3; // 運営にて認証NG

}
