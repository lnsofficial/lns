<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "Match.php" );
require_once( PATH_MODEL . "League.php" );

class Ladder extends Base{
	const MAIN_TABLE	= "ladders";
	const COL_ID		= "id";
	
	// カラム
	const DATA	= [
		"id"	=> [ "type" => "int"	, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"	=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"league_id"	=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"term"		=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"point"		=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
	
	public function getLadderTeamList(){
		$sSelectMatchSql = "SELECT * FROM " . Match::MAIN_TABLE . " WHERE match_date BETWEEN DATE_FORMAT(NOW() - INTERVAL " . INTERVAL_BATCH_TIME . ", '%Y-%m-%d 06:00:00') AND DATE_FORMAT(NOW() , '%Y-%m-%d 06:00:00') ORDER BY match_date ASC";
		
		$oResult = $oDb->execute( $sSelectMatchSql );
		
		return $oResult;
	}
	
	public function getLadderRanking( $oDb ){
		// サブクエリどっちに使ったほうが早かったっけ？
		$sSelectLadderSql = "SELECT tlr.team_id,tlr.league_id,tlr.term,tlr.point,ml.league_name,ml.league_tag,ml.rank  FROM " . self::MAIN_TABLE . " tlr LEFT JOIN " . League::MAIN_TABLE . " ml ON tlr.league_id = ml." . League::COL_ID . "  WHERE term = (SELECT MAX(term) FROM " . self::MAIN_TABLE . ") ORDER BY ml.rank ASC,tlr.point DESC, tlr." . self::COL_ID . " DESC";
		
		$oResult = $oDb->execute( $sSelectLadderSql );
		
		return $oResult;
	}
	
	public function getCurrentTerm( $oDb ){
        $sSelectTermSql = "SELECT MAX(term) as term FROM " . self::MAIN_TABLE;
        $oTerm = $oDb->execute( $sSelectTermSql );
        
        while( $row = $oTerm->fetch_assoc() ) {
            $iTerm = $row["term"];
        }
        if( $iTerm == null ){
            $iTerm = 1;
        }
        
        return $iTerm;
	}

	public function getLadderInfoByTerm( $oDb, $team ){
	    $sSelectLadderSql = "SELECT * FROM t_ladder_ranking WHERE term = ?";
	    $ahsParameter = [ $team ];
	    
	    $oLadderRanking = $oDb->executePrepare( $sSelectLadderSql, "i", $ahsParameter );
	    
        $ret = null;
	    while( $row = $oLadderRanking->fetch_assoc() ) {
            $ret[] = $row;
        }
        return $ret;
	}
}
