<?php

namespace App\Models;

class ApiQueue extends BaseModel
{
    protected $table      = 'api_queues';  // テーブル名
    protected $guarded    = [];

    const ACTION_UPDATE_SUMMONER        = 1;  // サモナーネーム変更時のriotApiへの問い合わせ
    const ACTION_REGISTER_SUMMONER      = 2;  // ユーザー初回登録時のriotApiへの問い合わせ

    const ACTION_MESSAGES = [
        self::ACTION_UPDATE_SUMMONER      => 'サモナーネーム変更',
        self::ACTION_REGISTER_SUMMONER    => 'ユーザー初回登録',
    ];

    const STATE_UNTREATED               = 0;
    const STATE_DOING                   = 1;
    const STATE_FAILED                  = 2;
    const STATE_FINISHED                = 3;

    const STATE_MESSAGES = [
        self::STATE_UNTREATED      => '未実行',
        self::STATE_DOING          => '実行中',
        self::STATE_FAILED         => '失敗',
        self::STATE_FINISHED       => '完了',
    ];

    const STATE_COLOR_CLASS = [
        'background-color' => [
            self::STATE_UNTREATED    => '#a9a9a9',  // darkgray
            self::STATE_DOING        => '#7fffd4',  // aquamarine
            self::STATE_FAILED       => '#ff0000',  // red
            self::STATE_FINISHED     => '#00ffff',  // cyan
        ],
    ];


    /**
     * 画面表示まわり
     */
    public function viewAction()
    {
        return self::ACTION_MESSAGES[$this->action];
    }
    public function viewState()
    {
        return self::STATE_MESSAGES[$this->state];
    }
    public function viewPayloadLink()
    {
        // 今のところ、{"user_id":1} この形だけなので以下にて。
        $payload = json_decode($this->payload, true);
        return url('/user/detail/' . $payload['user_id']);
    }
    public function viewResult()
    {
        return str_limit($this->result, 30);
    }

}
