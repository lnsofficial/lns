<?php
require_once( PATH_MODEL . "Base.php" );

class League extends Base{
	const MAIN_TABLE	= "m_league";
	const COL_ID		= "league_id";
	
	// カラム
	const DATA	= [
		"league_id"		=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"league_name"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"league_tag"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"rank"			=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
	
	// 一つ上のリーグ取得
	public function getUpperOneLeague( $oDb ){
		$sSelectLeagueSql = "SELECT * FROM m_league WHERE rank = ?";
		$ahsParameter = [ $this->rank - 1 ];
		
		$oResult = $oDb->executePrepare( $sSelectLeagueSql, "i", $ahsParameter );
		
		$oLeague = null;
		while( $row = $oResult->fetch_array() ){
			$oLeague = new League( $oDb, $row["league_id"] );
			break;
		}
		
		return $oLeague;
	}
	
	// 一つ下のリーグ取得
	public function getUnderOneLeague( $oDb ){
		$sSelectLeagueSql = "SELECT * FROM m_league WHERE rank = ?";
		$ahsParameter = [ $this->rank + 1 ];
		
		$oResult = $oDb->executePrepare( $sSelectLeagueSql, "i", $ahsParameter );
		
		$oLeague = null;
		while( $row = $oResult->fetch_array() ){
			$oLeague = new League( $oDb, $row["league_id"] );
			break;
		}
		
		return $oLeague;
	}
}
