<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "TeamStaffs.php" );
require_once( PATH_MODEL . "TeamJoin.php" );
require_once( PATH_MODEL . "Ladder.php" );

class Teams extends Base{
	const MAIN_TABLE			= "teams";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_name"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_name_kana"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_tag"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"team_tag_kana"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"status"		    => [ "type" => "tinyint"	, "min" => 0	,"max" => 127			, "required" => false	, "null" => false	],
	];


	const COUNT_MAX_MEMBER      = 10;        // チームに所属できるメンバーの最大数
	const COUNT_MAX_CONTACT     =  1;        // チームに所属できる連絡者の最大数
	const COUNT_MAX_STAFF       =  1;        // チームに所属できるアナリストの最大数


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
		$ahsParameter = [ $this->team_id, TeamJoin::STATE_ENABLE ];
		
		$oResult = $this->db->executePrepare( $sSelectLastJoin, "ii", $ahsParameter );
		
		$oLastJoin = null;
		while( $row = $oResult->fetch_array() ){
			$iLastJoinId = $row["id"];
			$oLastJoin = new LastJoin( $oDb, $iLastJoinId );
			break;
		}
		
		return $oLastJoin;
	}
	
	public function getLeague( $oDb ){
		$oLadder = $this->getCurrentLadder( $oDb );
		$oLeague = new League( $oDb, $oLadder->league_id );
		
		return $oLeague;
	}
	
	public function getCurrentLadder( $oDb ){
		$sSelectLadder = "SELECT * FROM " . Ladder::MAIN_TABLE . " WHERE team_id = ? ORDER BY term DESC";
		$ahsParameter = [ $this->id ];
		$oResult = $oDb->executePrepare( $sSelectLadder, "i", $ahsParameter );
		
		$oLadder = null;
		while( $row = $oResult->fetch_array() ){
			$oLadder = new Ladder( $oDb, $row["id"] );
			break;
		}
		
		return $oLadder;
	}
}
