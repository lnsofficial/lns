<?php
ini_set('display_errors', 1);

require_once('../lib/common/Define.php');
require_once( PATH_MODEL . "Match.php" );
require_once( PATH_MODEL . "Team.php" );
require_once( PATH_MODEL . "League.php" );
require_once( PATH_MODEL . "LadderRanking.php" );
// ポイントを反映
writeLog("-----------------【処理開始】-----------------");
// マッチ一覧取得
$oDb = new Db();

// トランザクション開始
$oDb->beginTransaction();

// TODO エラーハンドリング
// 次週分のラダー一覧作成
createNewLadder( $oDb );

// ラダー反映
updateLadderRanking( $oDb );

$oDb->commit();

writeLog("-----------------【処理終了】-----------------");

function createNewLadder( $oDb ){
	writeLog("[createNewLadder][Start]");
	$sSelectTermSql = "SELECT MAX(term) as term FROM t_ladder_ranking";
	$oTerm = $oDb->execute( $sSelectTermSql );
	
	$iTerm = 0;
	while( $row = $oTerm->fetch_assoc() ) {
		$iTerm = $row["term"];
	}
	
	if( $iTerm == 0 ){
		return false;
	}
	
	$sSelectLadderSql = "SELECT * FROM t_ladder_ranking WHERE term = ?";
	$ahsParameter = [ $iTerm ];
	
	$oLadderRanking = $oDb->executePrepare( $sSelectLadderSql, "i", $ahsParameter );
	
	while( $row = $oLadderRanking->fetch_assoc() ) {
		writeLog( "[createNewLadder][TeamId:" . $row["team_id"] . "]" );
		// 現在のラダーから次週のベース作成
		$oNewLadder = new LadderRanking( $oDb );
		
		$oNewLadder->team_id = $row["team_id"];
		$oNewLadder->league_id = $row["league_id"];
		$oNewLadder->point = $row["point"];
		$oNewLadder->term = $iTerm + 1;
		$oNewLadder->save();
	}
	
	writeLog("[createNewLadder][End]");
	return true;
}

function updateLadderRanking( $oDb ){
	writeLog("[updateLadderRanking][Start]");
	
	$oLastWeekMatch = Match::getMatchLastWeek( $oDb );
	while( $row = $oLastWeekMatch->fetch_assoc() ) {
		$iMatchId = $row["id"];
		writeLog("[updateLadderRanking][MatchId:" . $iMatchId . "]Start");
		
		$iWinnerTeamId = $row["winner"];
		
		$oHostTeam = new Team( $oDb, $row["host_team_id"] );
		$oApplyTeam = new Team( $oDb, $row["apply_team_id"] );
		
		$oWinTeam = $oHostTeam->team_id == $iWinnerTeamId ? $oHostTeam : $oApplyTeam;
		$oLoseTeam = $oHostTeam->team_id != $iWinnerTeamId ? $oHostTeam : $oApplyTeam;
		
		$iState = $row["state"];
		
		writeLog("[updateLadderRanking][MatchId:" . $iMatchId . "]WinnerId:" . $iWinnerTeamId . ",WinnerName:" . $oWinTeam->team_name . "");
		
		if( $iState == Match::MATCH_STATE_FINISHED ){
			updateTeamLadderNormal( $oDb, $oWinTeam, $oLoseTeam );
		} else if( $iState == Match::MATCH_STATE_ABSTAINED ){
			if( $row["host_team_id"] !== 0 && $row["apply_team_id"] !== 0 ){
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
	
	$iRankDiff = $oWinLeague->rank - $oLoseLeague->rank;
	
	switch( $iRankDiff ){
		case 0:
			// 同ブロック
			writeLog("[updateTeamLadderNormal][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]+2 Point");
			writeLog("[updateTeamLadderNormal][TeamId:" . $oLoseTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]-2 Point");
			$oWinLadder->point += 2;
			if( $oLoseLeague->rank == 8 && $oLoseTeam->point < 2 ){
				$oLoseLadder->point = 0;
			} else {
				$oLoseLadder->point -= 2;
			}
			
			break;
		case 1:
			// １つ上のブロック
			writeLog("[updateTeamLadderNormal][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]+3 Point");
			writeLog("[updateTeamLadderNormal][TeamId:" . $oLoseTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]-2 Point");
			$oWinLadder->point += 3;
			if( $oLoseLeague->rank == 8 && $oLoseTeam->point < 2 ){
				$oLoseLadder->point = 0;
			} else {
				$oLoseLadder->point -= 2;
			}
			break;
		case $iRankDiff < 0:
			// 下位ブロック
			writeLog("[updateTeamLadderNormal][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]+2 Point");
			writeLog("[updateTeamLadderNormal][TeamId:" . $oLoseTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]-1 Point");
			$oWinLadder->point += 2;
			if( $oLoseLeague->rank == 8 && $oLoseTeam->point < 1 ){
				$oLoseLadder->point = 0;
			} else {
				$oLoseLadder->point -= 1;
			}
			break;
		case $iRankDiff > 1:
			// ２つ以上上のブロック
			// 相手の一つ下のリーグ取得
			$oWinLeague = $oLoseLeague->getUnderOneLeague( $oDb );
			$oWinLadder->league_id = $oWinLeague->league_id;
			writeLog("[updateTeamLadderNormal][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]Up LeagueId:" . $oWinLadder->league_id);
			writeLog("[updateTeamLadderNormal][TeamId:" . $oLoseTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]-2 Point");
			$oWinLadder->point = 0;
			if( $oLoseLeague->rank == 8 && $oLoseTeam->point < 2 ){
				$oLoseLadder->point = 0;
			} else {
				$oLoseLadder->point -= 2;
			}
			break;
	}
	
	if( $oWinLadder->point >= 5 ){
		$oWinLeague = $oWinLeague->getUpperOneLeague( $oDb );
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]Up");
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]LeagueId:" . $oWinLeague->league_id);
		$oWinLadder->league_id = $oWinLeague->league_id;
		$oWinLadder->point = 0;
	}
	if( $oLoseLadder->point <= -5 ){
		$oLoseLeague = $oLoseLeague->getUnderOneLeague( $oDb );
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]Down");
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]LeagueId:" . $oLoseLeague->league_id);
		$oLoseLadder->league_id = $oLoseLeague->league_id;
		$oLoseLadder->point = 0;
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
	
	writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]+1 Point");
	writeLog("[updateTeamLadder][TeamId:" . $oLoseTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]-1 Point");
	$oWinLadder->point += 1;
	if( $oLoseLeague->rank == 8 && $oLoseTeam->point < 0 ){
		$oLoseLadder->point = 0;
	} else {
		$oLoseLadder->point -= 1;
	}
	
	if( $oWinLadder->point >= 5 ){
		$oWinLeague = $oWinLeague->getUpperOneLeague( $oDb );
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]Up");
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oWinTeam->team_name . "]LeagueId:" . $oWinLeague->league_id);
		$oWinLadder->league_id = $oWinLeague->league_id;
		$oWinLadder->point = 0;
	}
	if( $oLoseLadder->point <= -5 ){
		$oLoseLeague = $oLoseLeague->getUnderOneLeague( $oDb );
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]Down");
		writeLog("[updateTeamLadder][TeamId:" . $oWinTeam->team_id . ",TeamName:" . $oLoseTeam->team_name . "]LeagueId:" . $oLoseLeague->league_id);
		$oLoseLadder->league_id = $oLoseLeague->league_id;
		$oLoseLadder->point = 0;
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