<?php

namespace App\Models;

class Team extends BaseModel
{
    protected $table      = 'teams';  // テーブル名
    protected $guarded    = [];

    private $league = '';

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

    const ROLE_TOP                      = 1;
    const ROLE_JUNGLE                   = 2;
    const ROLE_MID                      = 3;
    const ROLE_ADC                      = 4;
    const ROLE_SUPPORT                  = 5;

    const ROLE_LABELS = [
        self::ROLE_TOP                  => 'Top',
        self::ROLE_JUNGLE               => 'Jg',
        self::ROLE_MID                  => 'Mid',
        self::ROLE_ADC                  => 'Adc',
        self::ROLE_SUPPORT              => 'Sup',
    ];


    /**
     * リレーション周り
     */
    public function members()
    {
        return $this->hasMany( 'App\Models\TeamMember', 'team_id', 'id' );
    }
    public function ladders()
    {
        return $this->hasMany( 'App\Models\Ladder', 'team_id', 'id' );
    }


    /**
     * 画面表示まわり
     */
    public function viewTeamName()
    {
        return str_limit($this->team_name, 30);
    }


    /**
     * メンバーそれぞれのランクから、平均出す。UNRANKは換算しない。
     */
    public function memberRankPointAvr()
    {
        $member_count_sum = 0;
        $point_sum        = 0;
        foreach( $this->members as $member )
        {
            if( $member->user->rank()->tier == 'UNRANK' )
            {
                continue;
            }
            $member_count_sum += 1;
            $point_sum        += UserRank::RANK_LIST[$member->user->rank()->tier][$member->user->rank()->rank];
        }

        return ( $member_count_sum ) ? round( $point_sum / $member_count_sum, 3 ) : 0;
    }


    public function league()
    {
        if( !empty($this->league) )
        {
            return $this->league;
        }
        $point_avr = $this->memberRankPointAvr();

        $this->league = League::where('point', '<=', $point_avr)
                              ->orderBy('point', 'desc')
                              ->first();

        return $this->league;
    }



}
