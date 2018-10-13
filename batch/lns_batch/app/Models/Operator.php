<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Operator extends Authenticatable
{
    use Notifiable;

    protected $table      = 'operators';  // テーブル名

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    const ACTIVATE_STATUS_UNAUTHENTICATED      = 0; // 未認証
    const ACTIVATE_STATUS_AUTHENTICATED        = 1; // 認証済み

    const ACTIVATE_STATUS_MESSAGES = [
        self::ACTIVATE_STATUS_UNAUTHENTICATED   => '未認証',
        self::ACTIVATE_STATUS_AUTHENTICATED     => '認証済み',
    ];

    const ACTIVATE_STATUS_COLOR_CLASS = [
        'table' => [
            self::ACTIVATE_STATUS_UNAUTHENTICATED   => 'warning',
            self::ACTIVATE_STATUS_AUTHENTICATED     => 'success',
        ],
    ];



}
