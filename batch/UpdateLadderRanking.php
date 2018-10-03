<?php
ini_set('display_errors', 1);

//require_once('../lib/common/Define.php');
require_once('/var/www/html/lib/common/Define.php');
require_once( "/var/www/html/lib/Model/Match.php" );
require_once( "/var/www/html/lib/Model/Teams.php" );
require_once( "/var/www/html/lib/Model/League.php" );
require_once( "/var/www/html/lib/Model/Ladder.php" );
require_once( "/var/www/html/lib/Model/LadderMovePoint.php" );
#require_once( PATH_MODEL . "Match.php" );
#require_once( PATH_MODEL . "Teams.php" );
#require_once( PATH_MODEL . "League.php" );
#require_once( PATH_MODEL . "Ladder.php" );
require_once( PATH_MODEL . "LadderMovePoint.php" );
// ポイントを反映
writeLog("-----------------【処理開始】-----------------");
// マッチ一覧取得
$oDb = new Db();

// トランザクション開始
$oDb->beginTransaction();

// TODO エラーハンドリング
// 次週分のラダー一覧作成
$ret = createNewLadder( $oDb );
if ($ret === false) {
    writeLog("-----------------【異常終了】-----------------");
    exit;
}

// ラダー反映
updateLadderRanking( $oDb );

$oDb->commit();

writeLog("-----------------【処理終了】-----------------");

function createNewLadder( $oDb ){
	writeLog("[createNewLadder][Start]");
    $iTerm = Ladder::getCurrentTerm( $oDb );
	
	if( $iTerm == 0 ){
		return false;
	}

    $ladder_infos = Ladder::getLadderInfoByTerm($oDb, $iTerm);

	if(empty($ladder_infos)){
		return false;
	}

    foreach ($ladder_infos as $info) {
		writeLog( "[createNewLadder][TeamId:" . $info["team_id"] . "]" );
		// 現在のラダーから次週のベース作成
		$oNewLadder = new Ladder( $oDb );
		
		$oNewLadder->team_id = $info["team_id"];
		$oNewLadder->league_id = $info["league_id"];
		$oNewLadder->season = SEASON_NOW;
		$oNewLadder->point = $info["point"];
		$oNewLadder->term = $iTerm + 1;
		$oNewLadder->save();
    }
	
	writeLog("[createNewLadder][End]");
	return true;
}

function updateLadderRanking( $oDb ){
	writeLog("[updateLadderRanking][Start]");
	
	$oLastDayMatch = Match::getMatchLastDay( $oDb );

    foreach ($oLastDayMatch as $match) {
		$iMatchId = $match["id"];
		writeLog("[updateLadderRanking][MatchId:" . $iMatchId . "]Start");
		
		$iWinnerTeamId = $match["winner"];
		
		$oHostTeam = new Teams( $oDb, $match["host_team_id"] );
		$oApplyTeam = new Teams( $oDb, $match["apply_team_id"] );
		
		$oWinTeam = $oHostTeam->id == $iWinnerTeamId ? $oHostTeam : $oApplyTeam;
		$oLoseTeam = $oHostTeam->id != $iWinnerTeamId ? $oHostTeam : $oApplyTeam;

		$iState = $match["state"];
		
		writeLog("[updateLadderRanking][MatchId:" . $iMatchId . "]WinnerId:" . $iWinnerTeamId . ",WinnerName:" . $oWinTeam->team_name . "");
		
		if( $iState == Match::MATCH_STATE_FINISHED ){
			updateTeamLadderNormal( $oDb, $oWinTeam, $oLoseTeam );
		} else if( $iState == Match::MATCH_STATE_ABSTAINED ){
			if( $match["host_team_id"] !== 0 && $match["apply_team_id"] !== 0 ){
				updateTeamLadderCancel( $oDb, $oWinTeam, $oLoseTeam );
			}
		}
		
		writeLog("[updateLadderRanking][MatchId:" . $iMatchId . "]End");
    }
	
	writeLog("[updateLadderRanking][End]");
}

function updateTeamLadderNormal( $oDb, $oWinTeam, $oLoseTeam ){
	writeLog("[updateTeamLadderNormal][Start]");
	// ラダー取得
	$oWinLadder = $oWinTeam->getCurrentLadder( $oDb );
	$oLoseLadder = $oLoseTeam->getCurrentLadder( $oDb );
	
	// リーグ取得
	$oWinLeague = new League( $oDb, $oWinLadder->league_id );
	$oLoseLeague = new League( $oDb, $oLoseLadder->league_id );

    $move_point = LadderMovePoint::getLeagueMovePoint($oWinLeague->rank, $oLoseLeague->rank);
    if (!$move_point) {
        return false;
    }
    writeLog("[updateTeamLadderNormal][TeamId:" . $oWinTeam->id . ",TeamName:" . $oWinTeam->team_name . "]+" . $move_point['win_point'] . "Point");
    writeLog("[updateTeamLadderNormal][TeamId:" . $oLoseTeam->id . ",TeamName:" . $oLoseTeam->team_name . "]+" . $move_point['lose_point'] . "Point");
    $moved_win_point = $oWinLadder->point + $move_point['win_point'];
    $moved_lose_point = $oLoseLadder->point + $move_point['lose_point'];

	switch( $moved_win_point ){
        case $moved_win_point > 100:
            if ($oWinLeague->rank == League::LEAGUE_SENMU) {
                $oWinLadder->point = $moved_win_point;
            } else {
                $oWinLadder->point = 0;
                $oWinLeague = $oWinLeague->getUpperOneLeague( $oDb );
                $oWinLadder->league_id = $oWinLeague->id;
            }
            break;
        default:
            $oWinLadder->point = $moved_win_point;
            break;
    }

	switch( $moved_lose_point ){
        case $moved_lose_point < 0:
            if ($oLoseLeague->rank == League::LEAGUE_HIRA) {
                $oLoseLadder->point = 0;
            } elseif ($moved_lose_point <= -10) {
                $oLoseLadder->point = 0;
                $oLoseLeague = $oLoseLeague->getUnderOneLeague( $oDb );
                $oLoseLadder->league_id = $oLoseLeague->id;
            } else {
                $oLoseLadder->point = $moved_lose_point;
            }
            break;
        default:
            $oLoseLadder->point = $moved_lose_point;
            break;
    }
	
	$oWinLadder->save();
	$oLoseLadder->save();
	
	writeLog("[updateTeamLadderNormal][End]");
}

function updateTeamLadderCancel( $oDb, $oWinTeam, $oLoseTeam ){
	writeLog("[updateTeamLadderCancel][Start]");
	// ラダー取得
	$oWinLadder = $oWinTeam->getCurrentLadder( $oDb );
	$oLoseLadder = $oLoseTeam->getCurrentLadder( $oDb );
	
	// リーグ取得
	$oWinLeague = new League( $oDb, $oWinLadder->league_id );
	$oLoseLeague = new League( $oDb, $oLoseLadder->league_id );
	
	$iRankDiff = $oWinLeague->rank - $oLoseLeague->rank;
	
	writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->id . ",TeamName:" . $oWinTeam->team_name . "]+1 Point");
	writeLog("[updateTeamLadder][TeamId:" . $oLoseTeam->id . ",TeamName:" . $oLoseTeam->team_name . "]-1 Point");
	$oWinLadder->point += 1;
	if( $oLoseLeague->rank == League::LEAGUE_HIRA && $oLoseLadder->point < 1 ){
		$oLoseLadder->point = 0;
	} else {
		$oLoseLadder->point -= 1;
	}
	
	if( $oWinLadder->point >= 5 ){
		$oWinLeague = $oWinLeague->getUpperOneLeague( $oDb );
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->id . ",TeamName:" . $oWinTeam->team_name . "]Up");
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->id . ",TeamName:" . $oWinTeam->team_name . "]LeagueId:" . $oWinLeague->id);
		$oWinLadder->league_id = $oWinLeague->id;
		$oWinLadder->point = 0;
	}
	if( $oLoseLadder->point <= -5 ){
		$oLoseLeague = $oLoseLeague->getUnderOneLeague( $oDb );
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->id . ",TeamName:" . $oLoseTeam->team_name . "]Down");
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->id . ",TeamName:" . $oLoseTeam->team_name . "]LeagueId:" . $oLoseLeague->id);
		$oLoseLadder->league_id = $oLoseLeague->id;
		$oLoseLadder->point = 2;
	}
	
	$oWinLadder->save();
	$oLoseLadder->save();
	
	writeLog("[updateTeamLadderCancel][End]");
}

function writeLog( $sMessage ){
	$sLogFileName = getenv("LOG_DIR") . date("Ymd") . ".log";
	$sLogMessage = date("[Y/m/d H:i:s]") . $sMessage . "\n";
	
	error_log( $sLogMessage, 3, $sLogFileName );
}
