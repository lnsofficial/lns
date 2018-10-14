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

    const LOGO_STATUS_MESSAGES = [
        self::LOGO_STATUS_UNREGISTERED      => '未登録',
        self::LOGO_STATUS_UNAUTHENTICATED   => '未認証',
        self::LOGO_STATUS_AUTHENTICATED     => '認証済み',
        self::LOGO_STATUS_AUTHENTICATEERROR => '認証NG',
    ];

    const LOGO_STATUS_COLOR_CLASS = [
        'table' => [
            self::LOGO_STATUS_UNREGISTERED      => '',
            self::LOGO_STATUS_UNAUTHENTICATED   => 'warning',
            self::LOGO_STATUS_AUTHENTICATED     => 'success',
            self::LOGO_STATUS_AUTHENTICATEERROR => 'danger',
        ],
    ];


    /**
     * リレーション周り
     */
    public function members()
    {
        return $this->hasMany( 'App\Models\TeamMember', 'team_id', 'id' );
    }



}
