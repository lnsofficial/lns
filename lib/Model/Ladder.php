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
	    $sSelectLadderSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE term = ?";
	    $ahsParameter = [ $team ];
	    
	    $oLadderRanking = $oDb->executePrepare( $sSelectLadderSql, "i", $ahsParameter );
	    
        $ret = array();
	    while( $row = $oLadderRanking->fetch_assoc() ) {
            $ret[] = $row;
        }
        return $ret;
	}
}
