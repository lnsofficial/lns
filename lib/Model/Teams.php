<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "TeamStaffs.php" );
require_once( PATH_MODEL . "TeamJoin.php" );
require_once( PATH_MODEL . "Ladder.php" );
require_once( PATH_MODEL . "TeamContact.php" );
require_once( PATH_MODEL . "TeamOwner.php" );

class Teams extends Base{
    const MAIN_TABLE            = "teams";
    const COL_ID                = "id";
    
    // カラム
    const DATA  = [
        "id"                => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "user_id"           => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "team_name"         => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "team_name_kana"    => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "team_tag"          => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "team_tag_kana"     => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "comment"           => [ "type" => "varchar"    , "min" => 1    ,"max" => 256           , "required" => false   , "null" => true    ],
        "status"            => [ "type" => "int"        , "min" => 0    ,"max" => 127           , "required" => false   , "null" => false   ],
        "logo_status"       => [ "type" => "int"        , "min" => 0    ,"max" => 127           , "required" => false   , "null" => false   ],
        "logo_updated_at"   => [ "type" => "varchar"    , "min" => 0    ,"max" => 256           , "required" => false   , "null" => true    ],
    ];


    const COUNT_MAX_MEMBER      = 10;        // チームに所属できるメンバーの最大数
    const COUNT_MAX_CONTACT     =  1;        // チームに所属できる連絡者の最大数
    const COUNT_MAX_STAFF       =  1;        // チームに所属できるアナリストの最大数
    const COUNT_MIN_JOIN_LADDER =  5;        // 大会に参加可能な最低選手人数

    const LOGO_MAX_WIDTH        =512;        // チームロゴアップロード可能な最大横
    const LOGO_MAX_HEIGHT       =512;        // チームロゴアップロード可能な最大縦

    const LOGO_STATUS_UNREGISTERED      = 0; // 未登録
    const LOGO_STATUS_UNAUTHENTICATED   = 1; // アップロードしたけどまだ運営で未認証
    const LOGO_STATUS_AUTHENTICATED     = 2; // 運営にて認証済み
    const LOGO_STATUS_AUTHENTICATEERROR = 3; // 運営にて認証NG

    /**
     * // ロゴファイル名はここから取る感じで。
     * 
     * @return string
     */
    function getLogoFileName()
    {
        $file_name = self::makeLogoFileName( $this->id, $this->team_tag );
        $file_path = PATH_TEAM_LOGO . $file_name;
        if( !file_exists($file_path) )
        {
            $file_name = "0_general.png";
        }

        return $file_name;
    }
    static function makeLogoFileName( $team_id, $team_tag )
    {
        return $team_id . "_" . $team_tag . ".png";
    }


    /**
     * // 配信用ロゴファイル名はここから取る感じで。
     * 
     * team.logo_status での判定
     * /img/logo/modified/*_logo.png有無の判定
     * /img/logo/*_logo.png有無の判定
     * で出し分ける
     * 
     * @return string
     */
    function getStreamingLogoFileName()
    {
        $logo_img_path = "/img/logo/";
        $file_name = $this->getLogoFileName();

        switch( $this->logo_status )
        {
            // 運営にて認証済みの場合
            case self::LOGO_STATUS_AUTHENTICATED:
                // 修正版がある場合はそちらから。
                if( file_exists( PATH_TEAM_LOGO . "modified/" . $file_name ) )
                {
                    $logo_img_path .= "modified/";
                }

                $logo_file = $logo_img_path . $file_name;
                break;

            // 運営にて認証NGの場合
            case self::LOGO_STATUS_AUTHENTICATEERROR:
                $file_name = "0_ban.png";
                $logo_file = $logo_img_path . $file_name;
                break;

            // アップロードしたけどまだ運営で未認証の場合
            case self::LOGO_STATUS_UNAUTHENTICATED:
            // 未登録の場合
            case self::LOGO_STATUS_UNREGISTERED:
            default:
                $file_name = "0_general.png";
                $logo_file = $logo_img_path . $file_name;
                break;
        }

        return $logo_file;
    }


    /**
     * // 作成者で検索、だと思う。
     * 
     * @param  int                  $user_id                // users.id
     * @return Team
     */
    function getTeamFromUserId( $user_id )
    {
        $oDb = new Db();

        $sSelectUser = "SELECT * FROM teams WHERE user_id = ?";
        $ahsParameter = [ $user_id ];
        
        $oResult = $oDb->executePrepare( $sSelectUser, "i", $ahsParameter );
        
        $oTeam = $oResult->fetch_assoc();
        
        return $oTeam;
    }



    /**
     * // pk検索1レコード
     * 
     * @param  int                  $id                     // teams.id
     * @return Team
     */
    function find( $id )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM teams WHERE id = ?";
        $bindParam  = [$id];
        return $db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
    }
    /**
     * // pk検索複数レコード
     * 
     * @param  array                $ids                     // [teams.id, ...]
     * @return Teams[]
     */
    function getById( $ids )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM teams WHERE id IN (";
        $hatenas = '';
        $types   = '';
        foreach( $ids as $tid )
        {
            if( !empty($hatenas) )
            {
                $hatenas .= ',';
            }
            $hatenas .= '?';
            $types   .= 'i';
            $bindParam[]  = $tid;
        }
        $hatenas .= ')';
        $prepareSql .= $hatenas;
        $result = $db->executePrepare( $prepareSql, $types, $bindParam );
        $teams = [];
        while( $team = $result->fetch_assoc() )
        {
            $teams[] = $team;
        }

        return $teams;
    }
    // スタッフ取得
    public function getStaff(){
        $oDb = new Db();
        
        $ahsStaff = TeamStaffs::getList( $oDb, [ [ "column" => "team_id",  "type" => "varchar", "value" => $this->id ] ] );
        
        return $ahsStaff;
    }
    
    public function getSearchList(){
        $oDb = new Db();
        
        $ahsTeams = Teams::getList( $oDb, [ [ "column" => "status",  "type" => "int", "value" => 0 ] ] );

        // チームロゴ未登録のとこもある
        foreach( $ahsTeams as &$team )
        {
            $tmp_team = new Teams( $oDb, $team['id'] );
            $team['logo_file_name'] = $tmp_team->getLogoFileName();
        }

        return $ahsTeams;
    }
    
    public function getLastJoin( $oDb ){
        $sSelectLastJoin = "SELECT id FROM " . TeamJoin::MAIN_TABLE . " WHERE team_id = ? AND state = ? ORDER BY joined_at DESC";
        $ahsParameter = [ $this->id, TeamJoin::STATE_ENABLE ];
        
        $oResult = $this->db->executePrepare( $sSelectLastJoin, "ii", $ahsParameter );
        
        $oLastJoin = null;
        while( $row = $oResult->fetch_array() ){
            $iLastJoinId = $row["id"];
            $oLastJoin = new TeamJoin( $oDb, $iLastJoinId );
            break;
        }
        
        return $oLastJoin;
    }
    
    public function getLeague( $oDb ){
        $oLadder = $this->getCurrentLadder( $oDb );
        $oLeague = null;
        if( $oLadder ){
            $oLeague = new League( $oDb, $oLadder->league_id );
        }
        
        return $oLeague;
    }
    
    public function getCurrentLadder( $oDb ){
        $sSelectLadder = "SELECT * FROM " . Ladder::MAIN_TABLE . " WHERE team_id = ? AND season = '" . SEASON_NOW . "' ORDER BY term DESC";
        $ahsParameter = [ $this->id ];
        $oResult = $oDb->executePrepare( $sSelectLadder, "i", $ahsParameter );
        
        $oLadder = null;
        while( $row = $oResult->fetch_array() ){
            $oLadder = new Ladder( $oDb, $row["id"] );
            break;
        }
        
        return $oLadder;
    }
    
    public function getBeforeSeasonLadder()
    {
        $sSelectLadder = "SELECT * FROM " . Ladder::MAIN_TABLE . " WHERE team_id = ? AND season = '" . SEASON_BEFORE . "' ORDER BY term DESC";
        $ahsParameter = [ $this->id ];
        $oResult = $this->db->executePrepare( $sSelectLadder, "i", $ahsParameter );
        
        $oLadder = null;
        while( $row = $oResult->fetch_array() ){
            $oLadder = new Ladder( $this->db, $row["id"] );
            break;
        }
        
        return $oLadder;
    }

    public function getTeamMembers( $oDb ){
        $sSelectTeamMember = "SELECT tm.team_id,us.id,us.summoner_id,us.tier,us.rank FROM team_members tm LEFT JOIN users us ON tm.user_id = us.id  WHERE team_id = ?";
        $ahsParameter = [ $this->id ];
        
        $oResult = $oDb->executePrepare( $sSelectTeamMember, "i", $ahsParameter );
        
        $ahsTeamMembers = null;
        while( $row = $oResult->fetch_assoc() ){
            $ahsTeamMembers[] = $row;
        }
        
        return $ahsTeamMembers;
    }
    
    public function getTeamContact(){
        $teamContact = null;
        $db = new Db();
        
        $result = TeamContact::getList( $db, [ [ "column" => "team_id",  "type" => "int", "value" => $this->id ] ] );
        
        if( $result ){
            $teamContact = new TeamContact( $db, $result[0]["id"] );
        }
        
        return $teamContact;
    }
    
    public function getTeamOwner(){
        $teamContact = null;
        $db = new Db();
        
        $result = TeamOwner::getList( $db, [ [ "column" => "team_id",  "type" => "int", "value" => $this->id ] ] );
        if( $result ){
            $teamOwner = new TeamOwner( $db, $result[0]["id"] );
        }
        
        return $teamOwner;
    }

    public function isAuthorized( $userId ){
        $authorized = false;
        
        $teamContact    = $this->getTeamContact();
        if( $teamContact && $teamContact->user_id == $userId ){
            $authorized = true;
        }
        
        $teamOwner      = $this->getTeamOwner();
        if( $teamOwner && $teamOwner->user_id == $userId ){
            $authorized = true;
        }
        
        return $authorized;
    }
    
    public function getTeamMemberInfoById( $user_id ){
        $ahsMemberInfo = [];
        
        // オーナーから取得
        $ahsOwnerParameter = [
            [ "column" => "team_id",  "type" => "int", "value" => $this->id ],
            [ "column" => "user_id",  "type" => "int", "value" => $user_id ],
        ];
        $ownerResult = TeamOwner::getList( $this->db, $ahsOwnerParameter );
        
        if( $ownerResult ){
            $teamOwner = new TeamOwner( $this->db, $ownerResult[0]["id"] );
            $ahsMemberInfo["owner"] = $teamOwner;
        }
        
        // メンバーから取得
        $ahsMemberParameter = [
            [ "column" => "team_id",  "type" => "int", "value" => $this->id ],
            [ "column" => "user_id",  "type" => "int", "value" => $user_id ],
        ];
        $memberResult = TeamMembers::getList( $this->db, $ahsMemberParameter );
        
        if( $memberResult ){
            $teamMember = new TeamMembers( $this->db, $memberResult[0]["id"] );
            $ahsMemberInfo["member"] = $teamMember;
        }
        
        // アナリストから取得
        $ahsStaffParameter = [
            [ "column" => "team_id",  "type" => "int", "value" => $this->id ],
            [ "column" => "user_id",  "type" => "int", "value" => $user_id ],
        ];
        $staffResult = TeamStaffs::getList( $this->db, $ahsStaffParameter );
        
        if( $staffResult ){
            $teamStaff = new TeamStaffs( $this->db, $staffResult[0]["id"] );
            $ahsMemberInfo["staff"] = $teamStaff;
        }
        
        // 連絡先から取得
        $ahsContactParameter = [
            [ "column" => "team_id",  "type" => "int", "value" => $this->id ],
            [ "column" => "user_id",  "type" => "int", "value" => $user_id ],
        ];
        $contactResult = TeamContact::getList( $this->db, $ahsContactParameter );
        
        if( $contactResult ){
            $teamContact = new TeamContact( $this->db, $contactResult[0]["id"] );
            $ahsMemberInfo["contact"] = $teamContact;
        }
        
        return $ahsMemberInfo;
    }
    
    public function enableTeamLeave( $user_id ){
        $ahsTeamMemberInfo = $this->getTeamMemberInfoById( $user_id );
        if( empty( $ahsTeamMemberInfo["member"] ) ){
            // チームに未所属
            return false;
        }
        
        return true;
        /*
        $season_start_date  = Settings::getSettingValue( Settings::SEASON_START_DATE );
        $season_end_date    = Settings::getSettingValue( Settings::SEASON_END_DATE );
        
        $replacement_start_date  = Settings::getSettingValue( Settings::REPLACEMENT_START_DATE );
        $replacement_end_date    = Settings::getSettingValue( Settings::REPLACEMENT_END_DATE );
        
        $current_date  = date( "Y-m-d H:i:s" );
        
        if( $current_date >= $season_start_date && $current_date <= $season_end_date ){
            // シーズン期間内
            if( $current_date >= $replacement_end_date || $current_date <= $replacement_start_date ){
                // 入れ替え期間外
                return false;
            }
        }
        
        return true;
        */
    }
}
