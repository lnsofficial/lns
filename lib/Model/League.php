<?php
require_once( PATH_MODEL . "Base.php" );

class League extends Base{
	const MAIN_TABLE	= "leagues";
	const COL_ID		= "id";

	const LEAGUE_SHACHO    = 1;
	const LEAGUE_SENMU     = 2;
	const LEAGUE_BUCHO     = 3;
	const LEAGUE_KACHO     = 4;
	const LEAGUE_KAKARICHO = 5;
	const LEAGUE_SHITSUCHO = 6;
	const LEAGUE_HIRA      = 7;
	
	// カラム
	const DATA	= [
		"id"		    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"league_name"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"league_tag"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"rank"			=> [ "type" => "int"	    , "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"point"			=> [ "type" => "int"	    , "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
	
	// 一つ上のリーグ取得
	public function getUpperOneLeague( $oDb ){
		$iRank = 0;
		if( $this->rank == 1 ){
			$iRank = $this->rank;
		}else{
			$iRank = $this->rank - 1;
		}
		$sSelectLeagueSql = "SELECT * FROM " . self::MAIN_TABLE ." WHERE rank = ?";
		$ahsParameter = [ $iRank ];
		
		$oResult = $oDb->executePrepare( $sSelectLeagueSql, "i", $ahsParameter );
		
		$oLeague = null;
		while( $row = $oResult->fetch_array() ){
			$oLeague = new League( $oDb, $row["id"] );
			break;
		}
		
		return $oLeague;
	}
	
	// 一つ下のリーグ取得
	public function getUnderOneLeague( $oDb ){
		$iRank = 0;
		if( $this->rank == self::LEAGUE_HIRA ){
			$iRank = $this->rank;
		}else{
			$iRank = $this->rank + 1;
		}
		
		$sSelectLeagueSql = "SELECT * FROM " . self::MAIN_TABLE ." WHERE rank = ?";
		$ahsParameter = [ $iRank ];
		
		$oResult = $oDb->executePrepare( $sSelectLeagueSql, "i", $ahsParameter );
		
		$oLeague = null;
		while( $row = $oResult->fetch_array() ){
			$oLeague = new League( $oDb, $row["id"] );
			break;
		}
		
		return $oLeague;
	}
	
	public function getAssignLeague( $oDb, $iTeamPower ){
		$oDb = new Db();
		
		$ahsParameter = [ [ "column" => "point",  "type" => "int", "value" => $iTeamPower , "operator" => "<=" ] ];
		$ahsOrder     = [ [ "column" => "point", "sort_order" => "DESC" ] ];
		$ahsResult = League::getList( $oDb, $ahsParameter, $ahsOrder );
		
		if( $ahsResult ){
			$oLeague = new League( $oDb, $ahsResult[0]["id"] );
		}
		
		return $oLeague;
	}
}
