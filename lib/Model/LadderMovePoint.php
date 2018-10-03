<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "Match.php" );
require_once( PATH_MODEL . "League.php" );

class LadderMovePoint extends Base{
    // 将来的にはmysqlにテーブル作りたい
	const MAIN_TABLE	= "ladder_move_point";
	
	// カラム
	const DATA	= [
		"win_rank"	=> [ "type" => "int"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"lose_rank"	=> [ "type" => "int"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"win_point"	=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"lose_point"=> [ "type" => "int"	, "min" => -256	,"max" => 0			    , "required" => true	, "null" => false	],
	];

    var $point_table = array(
        League::LEAGUE_HIRA => [
            League::LEAGUE_HIRA         => [ "win_point"  => 23, "lose_point" => -17, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 29, "lose_point" => -14, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 36, "lose_point" => -11, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 45, "lose_point" => -9,  ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 56, "lose_point" => -7,  ],
            League::LEAGUE_SENMU        => [ "win_point"  => 70, "lose_point" => -7,  ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 88, "lose_point" => -4,  ],
        ],
        League::LEAGUE_SHITSUCHO => [
            League::LEAGUE_HIRA         => [ "win_point"  => 18, "lose_point" => -23, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 22, "lose_point" => -18, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 28, "lose_point" => -14, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 34, "lose_point" => -9,  ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 56, "lose_point" => -7,  ],
            League::LEAGUE_SENMU        => [ "win_point"  => 70, "lose_point" => -7,  ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 88, "lose_point" => -4,  ],
        ],
        League::LEAGUE_KAKARICHO => [
            League::LEAGUE_HIRA         => [ "win_point"  => 13, "lose_point" => -30, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 17, "lose_point" => -24, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 21, "lose_point" => -19, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 26, "lose_point" => -15, ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 33, "lose_point" => -12, ],
            League::LEAGUE_SENMU        => [ "win_point"  => 41, "lose_point" => -10, ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 51, "lose_point" => -8,  ],
        ],
        League::LEAGUE_KACHO => [
            League::LEAGUE_HIRA         => [ "win_point"  => 10, "lose_point" => -39, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 13, "lose_point" => -31, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 16, "lose_point" => -25, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 20, "lose_point" => -20, ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 25, "lose_point" => -16, ],
            League::LEAGUE_SENMU        => [ "win_point"  => 31, "lose_point" => -13, ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 39, "lose_point" => -10, ],
        ],
        League::LEAGUE_BUCHO => [
            League::LEAGUE_HIRA         => [ "win_point"  => 8,  "lose_point" => -49, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 10, "lose_point" => -39, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 13, "lose_point" => -31, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 16, "lose_point" => -25, ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 20, "lose_point" => -20, ],
            League::LEAGUE_SENMU        => [ "win_point"  => 25, "lose_point" => -16, ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 31, "lose_point" => -13, ],
        ],
        League::LEAGUE_SENMU => [
            League::LEAGUE_HIRA         => [ "win_point"  => 7,  "lose_point" => -61, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 8,  "lose_point" => -49, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 10, "lose_point" => -39, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 13, "lose_point" => -31, ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 16, "lose_point" => -25, ],
            League::LEAGUE_SENMU        => [ "win_point"  => 20, "lose_point" => -20, ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 25, "lose_point" => -16, ],
        ],
        League::LEAGUE_SHACHO => [
            League::LEAGUE_HIRA         => [ "win_point"  => 5,  "lose_point" => -76, ],
            League::LEAGUE_SHITSUCHO    => [ "win_point"  => 7,  "lose_point" => -61, ],
            League::LEAGUE_KAKARICHO    => [ "win_point"  => 8,  "lose_point" => -49, ],
            League::LEAGUE_KACHO        => [ "win_point"  => 10, "lose_point" => -39, ],
            League::LEAGUE_BUCHO        => [ "win_point"  => 13, "lose_point" => -31, ],
            League::LEAGUE_SENMU        => [ "win_point"  => 16, "lose_point" => -25, ],
            League::LEAGUE_SHACHO       => [ "win_point"  => 20, "lose_point" => -20, ],
        ],
    );
	
	public function getLeagueMovePoint( $win_team_rank,  $lose_team_rank ){
        
		return $oResult;
	}
}
