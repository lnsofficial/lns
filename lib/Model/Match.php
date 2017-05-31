<?php
require_once( PATH_MODEL . "Base.php" );

class Match extends Base{
	const MAIN_TABLE	= "match_recruit_list";
	const COL_ID		= "id";
	
	// カラム
	const DATA	= [
		"id"					=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"host_team_id"			=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"apply_team_id"			=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"match_date"			=> [ "type" => "date"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"recruit_start_date"	=> [ "type" => "date"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"stream"				=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"type"					=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"state"					=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"winner"				=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"screen_shot_url"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
	];
	
	const MATCH_TYPE_ANY					= 1;
	const MATCH_TYPE_LESS_SAME				= 2;
	const MATCH_TYPE_LESS_ONE_ON_THE_SAME	= 3;
	
	const MATCH_STATE_DISABLED	= 0;
	const MATCH_STATE_RECRUIT	= 1;
	const MATCH_STATE_MATCHED	= 2;
	const MATCH_STATE_CANCEL	= 3;
	const MATCH_STATE_FINISHED	= 4;
	
	const MAX_MATCH_RECRUIT_COUNT = 4;
	
	public function getMatchLastWeek( $oDb ){
		$sSelectMatchSql = "SELECT * FROM match_recruit_list WHERE state = ? AND match_date BETWEEN DATE_FORMAT(NOW() - INTERVAL " . INTERVAL_BATCH_TIME . ", '%Y-%m-%d 06:00:00') AND DATE_FORMAT(NOW() , '%Y-%m-%d 06:00:00') ORDER BY match_date ASC";
		$ahsParameter = [ self::MATCH_STATE_FINISHED ];
		
		$oResult = $oDb->executePrepare( $sSelectMatchSql, "i", $ahsParameter );
		
		return $oResult;
	}
	
	public function getMatchList( $oDb, $ahsSearchOption ){
		$sSelectMatchSql = "SELECT * FROM match_recruit_list WHERE ";
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
			$asWhereSql[] = " state IN(?,?,?) OR (state = ? AND apply_team_id != 0) ";
			$ahsParameter[] = self::MATCH_STATE_RECRUIT;
			$ahsParameter[] = self::MATCH_STATE_MATCHED;
			$ahsParameter[] = self::MATCH_STATE_FINISHED;
			$ahsParameter[] = self::MATCH_STATE_CANCEL;
			$sType .= "iiii";
		}
		
		$sSelectMatchSql .= implode( " AND ", $asWhereSql );
		$sSelectMatchSql .= " ORDER BY create_date DESC";
		
		$oResult = $oDb->executePrepare( $sSelectMatchSql, $sType, $ahsParameter );
		
		return $oResult;
	}
}