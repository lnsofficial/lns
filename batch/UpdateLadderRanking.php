<?php
require_once('../lib/common/Define.php');
require_once( PATH_MODEL . "Match.php" );
require_once( PATH_MODEL . "Team.php" );
require_once( PATH_MODEL . "League.php" );
require_once( PATH_MODEL . "LadderRanking.php" );
// ポイントを反映
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

function createNewLadder( $oDb ){
	$sSelectTeamSql = "SELECT * FROM m_team";
	$oTeamList = $oDb->execute( $sSelectTeamSql );
	
	while( $row = $oTeamList->fetch_assoc() ) {
		$oTeam = new Team( $oDb, $row["team_id"] );
		
		// チームの現在のラダー取得
		$sSelectCurLadder = $oTeam->getCurrentLadder( $oDb );
		
		// 現在のラダーから次週のベース作成
		$oNewLadder = new LadderRanking( $oDb );
		$oNewLadder->team_id = $sSelectCurLadder->team_id;
		$oNewLadder->league_id = $sSelectCurLadder->league_id;
		$oNewLadder->point = $sSelectCurLadder->point;
		$oNewLadder->term = $sSelectCurLadder->term + 1;
		$oNewLadder->save();
	}
	
	return true;
}

function updateLadderRanking( $oDb ){
	$oLastWeekMatch = Match::getMatchLastWeek( $oDb );
	while( $row = $oLastWeekMatch->fetch_assoc() ) {
		$iWinnerTeamId = $row["winner"];
		
		$oHostTeam = new Team( $oDb, $row["host_team_id"] );
		$oApplyTeam = new Team( $oDb, $row["apply_team_id"] );
		
		$oWinTeam = $oHostTeam->team_id == $iWinnerTeamId ? $oHostTeam : $oApplyTeam;
		$oLoseTeam = $oHostTeam->team_id != $iWinnerTeamId ? $oHostTeam : $oApplyTeam;
		
		updateTeamLadder( $oDb, $oWinTeam, $oLoseTeam );
	}
}

function updateTeamLadder( $oDb, $oWinTeam, $oLoseTeam ){
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
			$oWinLadder->point += 2;
			$oLoseLadder->point -= 2;
			break;
		case 1:
			// １つ上のブロック
			$oWinLadder->point += 3;
			$oLoseLadder->point -= 2;
			break;
		case $iRankDiff < 0:
			// 下位ブロック
			$oWinLadder->point += 2;
			$oLoseLadder->point -= 1;
			break;
		case $iRankDiff > 1:
			// ２つ以上上のブロック
			// 相手の一つ下のリーグ取得
			$oWinLeague = $oLoseLeague->getUnderOneLeague( $oDb );
			$oWinLadder->league_id = $oWinLeague->league_id;
			$oWinLadder->point = 0;
			$oLoseLadder->point -= 2;
			break;
	}
	if( $oWinLadder->point >= 5 ){
		$oWinLeague = $oWinLeague->getUpperOneLeague( $oDb );
		$oWinLadder->league_id = $oWinLeague->league_id;
		$oWinLadder->point = 0;
	}
	if( $oLoseLadder->point <= -5 ){
		$oLoseLeague = $oLoseLeague->getUnderOneLeague( $oDb );
		$oLoseLadder->league_id = $oLoseLeague->league_id;
		$oLoseLadder->point = 0;
	}
	
	$oWinLadder->save();
	$oLoseLadder->save();
}