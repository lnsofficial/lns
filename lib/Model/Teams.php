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
        "status"            => [ "type" => "tinyint"    , "min" => 0    ,"max" => 127           , "required" => false   , "null" => false   ],
    ];


    const COUNT_MAX_MEMBER      = 10;        // チームに所属できるメンバーの最大数
    const COUNT_MAX_CONTACT     =  1;        // チームに所属できる連絡者の最大数
    const COUNT_MAX_STAFF       =  1;        // チームに所属できるアナリストの最大数
    const COUNT_MIN_JOIN_LADDER =  5;        // 大会に参加可能な最低選手人数


    /**
     * // ロゴファイル名はここから取る感じで。
     * 
     * @param  int                  $team_id                // teams.id
     * @return string
     */
    static function getLogoFileName( $team_id )
    {
        return $team_id . '_logo.jpg';
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
}
