<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "MatchCheckin.php" );

class Match extends Base{
    const MAIN_TABLE    = "matches";
    const COL_ID        = "id";
    
    // カラム
    const DATA  = [
        "id"                    => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "host_team_id"          => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "apply_team_id"         => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "match_date"            => [ "type" => "date"       , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "deadline_date"         => [ "type" => "date"       , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "recruit_start_date"    => [ "type" => "date"       , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "stream"                => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "type"                  => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "state"                 => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "winner"                => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "screen_shot_url"       => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "tournament_code"       => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "match_id"              => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "match_info"            => [ "type" => "varchar"    , "min" => 1    ,"max" => 2147483647    , "required" => false   , "null" => true    ],
        "url_youtube"           => [ "type" => "varchar"    , "min" => 1    ,"max" => 2147483647    , "required" => false   , "null" => true    ],
    ];
    
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
    
    const MAX_MATCH_RECRUIT_COUNT = 5;
    
    const FEATURE_GAME_COUNT       =  5; // topページの最新ゲームで最大何件表示させるか
    const FEATURE_GAME_DATE_BEFORE = 10; // topページの最新ゲームを取るときに、何日前までの試合を対象とするか

    const DISABLE_MATCH_HOUR_START = 4;
    const DISABLE_MATCH_HOUR_END   = 6;
  
    public function getMatchLastDay( $oDb ){
        $sSelectMatchSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE state IN(?,?) AND match_date BETWEEN DATE_FORMAT(NOW() - INTERVAL " . INTERVAL_BATCH_TIME . ", '%Y-%m-%d 06:00:00') AND DATE_FORMAT(NOW() , '%Y-%m-%d 06:00:00') ORDER BY match_date ASC";
        $ahsParameter = [ self::MATCH_STATE_FINISHED, self::MATCH_STATE_ABSTAINED ];
        
        $oResult = $oDb->executePrepare( $sSelectMatchSql, "ii", $ahsParameter );

        $ret = array();
        while ($row = $oResult->fetch_assoc()) {
            $ret[] = $row;
        }
        
        return $ret;
    }
    
    public function getMatchList( $oDb, $ahsSearchOption ){
        $sSelectMatchSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE ";
        $ahsParameter = [];
        $sType = "";
        $asWhereSql = [];
        
        $bStateSearch = false;
        if( $ahsSearchOption ){
            foreach( $ahsSearchOption as $value ){
                if( $value["value"] == null ){
                    break;
                }
                if( $value["column"] == "state" ){
                    $bStateSearch = true;
                }
                switch( $value["type"] ){
                    case "int":
                        $asWhereSql[] = $value["column"] . " = ? ";
                        $ahsParameter[] = $value["value"];
                        $sType .= "i";
                        break;
                    case "varchar":
                        $asWhereSql[] = $value["column"] . " = ? ";
                        $ahsParameter[] = $value["value"];
                        $sType .= "s";
                        break;
                    case "date":
                        $asWhereSql[] = $value["column"] . " " . $value["operator"] . " ? ";
                        $ahsParameter[] = $value["value"];
                        $sType .= "s";
                        break;
                }
            }
        }
        if( !$bStateSearch ){
            $asWhereSql[] = " state IN(?,?) OR (state = ? AND apply_team_id != 0) ";
            $ahsParameter[] = self::MATCH_STATE_RECRUIT;
            $ahsParameter[] = self::MATCH_STATE_MATCHED;
            $ahsParameter[] = self::MATCH_STATE_CANCEL;
            $sType .= "iii";
        }
        
        $sSelectMatchSql .= implode( " AND ", $asWhereSql );
        $sSelectMatchSql .= " ORDER BY created_at DESC";
        
        $oResult = $oDb->executePrepare( $sSelectMatchSql, $sType, $ahsParameter );
        
        return $oResult;
    }
    
    // 試合結果の登録可能な時間を過ぎてないかチェック
    public function expirationRegistMatchResult(){
        $bResult = true;
        
        $iMatchHour = (int)date('G', strtotime( $this->match_date ) );
        
        $sNextBatchDay = "";
        
        switch( $iMatchHour ){
            case $iMatchHour < 6:
                // 当日なので何もしない
                break;
            case $iMatchHour >= 6:
                // 翌日なので＋1日
                $sNextBatchDay = " + 1 day ";
                break;
        }
        
        // 試合日時後のバッチ開始予定時間
        $nextExecuteBatchDate = date('Y-m-d 06:00:00', strtotime( $this->match_date . $sNextBatchDay ) );
        
        if( date( 'Y-m-d H:i:s' ) > $nextExecuteBatchDate ){
            $bResult = false;
        }
        
        return $bResult;
    }
    
    // 試合結果をキャンセル登録可能な時間を過ぎてないかチェック
    public function enableCancel(){
        $bResult = true;
        // 試合日時の1日前はキャンセル不可
        $enableCancelDate = date('Y-m-d H:i:s', strtotime( $this->match_date . " - 24 hour" ) );
        
        if( date( 'Y-m-d H:i:s' ) > $enableCancelDate ){
            $bResult = false;
        }
        
        return $bResult;
    }
    
    // 当日キャンセル可能な時間かチェック
    public function enablePenaltyCancel(){
        $bResult = true;
        // 試合日時の1日以内
        $enableCancelDate = date('Y-m-d H:i:s', strtotime( $this->match_date . " - 24 hour" ) );
        $now = date( 'Y-m-d H:i:s' );

        if( $now < $enableCancelDate || $now > $this->match_date){
            $bResult = false;
        }
        
        return $bResult;
    }
    
    // 試合に参加可能かチェック
    public function enableJoin( $iHostRank, $iApplyRank ){
        $bEnableJoin = true;
        
        // 試合時間を過ぎていないかチェック
        if( date( 'Y-m-d H:i:s' ) > date( 'Y-m-d H:i:s', strtotime( $this->match_date ) ) ){
            $bEnableJoin = false;
        } elseif( $this->deadline_date && date( 'Y-m-d H:i:s' ) > date( 'Y-m-d H:i:s', strtotime( $this->deadline_date ) ) ){
            // 応募受付期限を過ぎていないかチェック
            $bEnableJoin = false;
        } else {
            switch( $this->type ){
                case Match::MATCH_TYPE_ANY:
                    // 何もしない
                    break;
                case Match::MATCH_TYPE_LESS_SAME:
                    // ホストのランクが自分のランクより下ならエラー
                    if( $iHostRank > $iApplyRank ){
                        $bEnableJoin = false;
                    }
                    break;
                case Match::MATCH_TYPE_LESS_ONE_ON_THE_SAME:
                    // ホストのランクが自分のランクから2つ以下ならエラー
                    if( $iHostRank > $iApplyRank + 1 ){
                        $bEnableJoin = false;
                    }
                    break;
                case Match::MATCH_TYPE_LESS_TWO_ON_THE_SAME:
                    // ホストのランクが自分のランクから2つ以下ならエラー
                    if( $iHostRank > $iApplyRank + 2 ){
                        $bEnableJoin = false;
                    }
                    break;
            }
        }
        
        return $bEnableJoin;
    }

    public function getMatchCountAtMonthByDate( $host_team_id, $date, $include_abstained = false ){
        $start_month    = date("Y-m-01", strtotime( $date ) );
        $end_month      = date("Y-m-01", strtotime( $date . " +1 month"));

        if ($include_abstained) {
            $sSelectMatchSql = "SELECT count(1) as cnt FROM " . self::MAIN_TABLE . " WHERE host_team_id = ? and ? <= match_date and match_date < ? AND state not in (?, ?)";
            $ahsParameter   = [ $host_team_id, $start_month, $end_month, self::MATCH_STATE_CANCEL,  self::MATCH_STATE_ABSTAINED];
            $sType = "issii";
        } else {
            $sSelectMatchSql = "SELECT count(1) as cnt FROM " . self::MAIN_TABLE . " WHERE host_team_id = ? and ? <= match_date and match_date < ? AND state <> ?";
            $ahsParameter   = [ $host_team_id, $start_month, $end_month, self::MATCH_STATE_CANCEL];
            $sType = "issi";
        }
        
        $oDb = new Db();
        $result = $oDb->executePrepare( $sSelectMatchSql, $sType, $ahsParameter );
        $row = $result->fetch_assoc();

        return $row["cnt"];
    }
    
    /**
     * チェックイン可能か
     */
    public function enableCheckin( $iTeamId ){
        $start_date    = date( "Y-m-d H:i:s", strtotime( $this->match_date . "-1 hour" ) );
        $end_date      = date( "Y-m-d H:i:s", strtotime( $this->match_date . "-10 minute" ) );
        $current_date  = date( "Y-m-d H:i:s" );
        
        if( $current_date >= $end_date || $current_date <= $start_date ){
            return false;
        }
        
        $ahsCheckin = $this->getCheckinByTeamId( $iTeamId );
        if( isset( $ahsCheckin ) && !empty($ahsCheckin) ){
            return false;
        }
        
        return true;
    }

    public function checkRecruitMatchDate($match_date){
        if (empty($match_date)) {
            return false;
        }
        
        $hour = date('H', strtotime($match_date));
        if (self::DISABLE_MATCH_HOUR_START <= $hour && $hour < self::DISABLE_MATCH_HOUR_END) {
            return false;
        }

        return true;
    }
    
    public function getCheckinStatus( $db = null ){
        $checkinStatus = false;
        if( empty( $db ) ){
            $db = new Db();
        }
        $host_team_checkins = $this->getCheckinByTeamId( $this->host_team_id, $db );
        $apply_team_checkins = $this->getCheckinByTeamId( $this->apply_team_id, $db );
        
        if( $host_team_checkins && $apply_team_checkins ){
            $checkinStatus = true;
        }
        
        return $checkinStatus;
    }

    /**
     * TODO モデル的にはマッチかチームのどっちかに入ってるべきな関数なのでその内移動
     * // チームのチェックイン一覧
     * 
     * @param  int                  $team_id                // teams.id
     * @return MatchCheckin[]
     */
    public function getCheckinByTeamId( $team_id, $db = null ){
        if( empty( $team_id ) ){
            return null;
        }
        
        if( empty( $db ) ){
            $db = new Db();
        }
        $ahsResult = MatchCheckin::getList(
            $db,
            [
                [ "column" => "match_id",  "type" => "int", "value" => $this->id ],
                [ "column" => "team_id",   "type" => "int", "value" => $team_id ]
            ]
        );
        
        $ahsMatchCheckins = [];
        foreach( $ahsResult as $hsResult ){
            $oUser                      = new User( $db, $hsResult["user_id"] );
            $hsResult["summoner_name"]  = $oUser->summoner_name;
            $hsResult["main_role"]      = $oUser->main_role;
            $ahsMatchCheckins[]         = $hsResult;
        }

        return $ahsMatchCheckins;
    }
    
    public function getCheckinMemberSummonerId( $db ){
        $asMatchCheckins = [];
        
        // 両チームのメンバーをセット
        $ahsResult = MatchCheckin::getList(
            $db,
            [
                [ "column" => "match_id",  "type" => "int", "value" => $this->id ]
            ]
        );
        
        foreach( $ahsResult as $hsResult ){
            $oUser              = new User( $db, $hsResult["user_id"] );
            $asMatchCheckins[]  = $oUser->summoner_id;
        }
        
        // 運営の観戦者をセット
        $ahsMatchObserversResult = ManagementObservers::getAllObservers();
        
        foreach( $ahsMatchObserversResult as $hsResult ){
            $asMatchCheckins[]  = $hsResult["summoner_id"];
        }
        
        return $asMatchCheckins;
    }
    
    public function getMatchByTournamentCode( $oDb, $tournament_code ){
        $match = null;
        
        $ahsResult = Match::getList(
            $oDb,
            [
                [ "column" => "tournament_code",  "type" => "varchar", "value" => $tournament_code ]
            ]
        );
        
        if( $ahsResult ){
            $match = new Match( $oDb, $ahsResult[0]["id"] );
        }
        return $match;
    }
    
    public function getMatchWinnerTeamBySummonerId( $summoner_id ){
        $winner_team_id = 0;
        
        $ahsHostMembers     = $this->getCheckinByTeamId( $this->host_team_id );
        $ahsApplyMembers    = $this->getCheckinByTeamId( $this->apply_team_id );
        
        if( self::getMatchTeamBySummonerId( $ahsHostMembers, $summoner_id) ){
            $winner_team_id = $this->host_team_id;
        }elseif( self::getMatchTeamBySummonerId( $ahsApplyMembers, $summoner_id) ){
            $winner_team_id = $this->apply_team_id;
        }
        
        return $winner_team_id;
    }
    
    public function getMatchTeamBySummonerId( $member_list, $summoner_id ){
        foreach( $member_list as $member ){
            if( $member["summoner_id"] == $summoner_id ){
                return true;
            }
        }
        return false;
    }
}
