<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Teams.php' );
// TODO 最低限の共通化、全コントローラーで共通部分はBaseControllerにまとめる
// 特別に処理を入れる場合のみ、各Controllerに追記する形で開発する

class TeamController extends BaseController{
	const INPUT_DATA = [
		
	];
    // tmp
    var $_user_id_tmp = 123;
	public function __construct(){
	}
	
	public function confirm(){
		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		// 画面表示
		self::_displayConfirm();
	}
	
	public function register(){
		// self::displayError();
		//TODO 一旦仮で塞ぐ、その内簡単に切り替えれるようにしたい
		// あと、リクエストをInsertメソッドで取ってるとこもその内修正
		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		// DBに登録
		if( !self::insert() ){
			self::displayError();
			exit;
		}
		
		// 画面表示
		self::_displayCommit();
	}
	
	// TODO バリデーション処理の実行、とりあえずは必須チェックだけ
	// あとBaseControllerあたりに共通化して置いとく
	private function validation(){
		$bResult	= true;
		if( !$_REQUEST["inputTeamNm"] ){
			$bResult = false;
		}
		if( !$_REQUEST["inputTeamNmKana"] ){
			$bResult = false;
		}
		if( !$_REQUEST["inputTeamTag"] ){
			$bResult = false;
		}
		if( !$_REQUEST["inputTeamTagKana"] ){
			$bResult = false;
		}
        return $bResult;
	}
	
	private function insert(){
		try{
            $oDb = new Db();
            $oDb->beginTransaction();
            $oTeams = new Teams( $oDb );
            $oTeams->user__id = $this->_user_id_tmp;
            $oTeams->team_name = $_REQUEST["inputTeamNm"];
            $oTeams->team_name_kana = $_REQUEST["inputTeamNmKana"];
            $oTeams->team_tag = $_REQUEST["inputTeamTag"];
            $oTeams->team_tag_kana = $_REQUEST["inputTeamTagKana"];
            $oTeams->save();
            $oDb->commit();
		} catch (Exception $e) {
			return false;
		}
		return true;
	    /*
		try{
			$mysqli	= new mysqli('localhost', DB_USER, DB_PASSWORD, DB_NAME);
			$mysqli->autocommit(False);
			
			if( $mysqli->connect_error ){
				echo $mysqli->connect_error;
				exit();
			}
			
			// チーム登録
			$sInsertTeamSql		= "INSERT INTO teams(user_id,team_name,team_name_kana,team_tag,team_tag_kana) VALUE(?,?,?,?,?)";
			$iTeamId = 0;
			if($stmt = $mysqli->prepare($sInsertTeamSql)){
				$user_id	    = $this->_user_id_tmp;
				$team_name		= $_REQUEST["inputTeamNm"];
				$team_name_kana	= $_REQUEST["inputTeamNmKana"];
				$team_tag		= $_REQUEST["inputTeamTag"];
				$team_tag_kana	= $_REQUEST["inputTeamTagKana"];
				$stmt->bind_param("sssss",$user_id,$team_name,$team_name_kana,$team_tag,$team_tag_kana);
				$stmt->execute();
				
				if( $stmt->error ){
					// TODO エラー処理
					echo $stmt->error;
				}else{
					$iTeamId	= $mysqli->insert_id;
				}
				
				$stmt->close();
			}
			
			$mysqli->commit();
			
			$mysqli->close();
		} catch (Exception $e) {
			return false;
		}
		return true;
*/
	}
	
	// 確認画面表示
	// TODO その内共通化
	private function _displayConfirm(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign("inputTeamNm", $_REQUEST["inputTeamNm"]);
		$smarty->assign("inputTeamNmKana", $_REQUEST["inputTeamNmKana"]);
		$smarty->assign("inputTeamTag", $_REQUEST["inputTeamTag"]);
		$smarty->assign("inputTeamTagKana", $_REQUEST["inputTeamTagKana"]);
		
		$smarty->display('Team/confirm.tmpl');
	}

	private function _displayCommit(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign("inputTeamNm", $_REQUEST["inputTeamNm"]);
		$smarty->assign("inputTeamNmKana", $_REQUEST["inputTeamNmKana"]);
		$smarty->assign("inputTeamTag", $_REQUEST["inputTeamTag"]);
		$smarty->assign("inputTeamTagKana", $_REQUEST["inputTeamTagKana"]);
		
		$smarty->display('Team/commit.tmpl');
	}
	
	// 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
	public function displayError(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('TeamRegister_err.tmpl');
	}
	
	public function detail(){
		$iTeamId = $_REQUEST["team_id"];
		
		$oDb = new Db();
		
		$oTeam = new Team( $oDb, $iTeamId );
		
		$aoTeamMember = $oTeam->getTeamMember( $oDb );
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "team_name"		, $oTeam->team_name );
		$smarty->assign( "team_name_kana"	, $oTeam->team_name_kana );
		$smarty->assign( "team_tag"			, $oTeam->team_tag );
		$smarty->assign( "team_tag_kana"	, $oTeam->team_tag_kana );
		$smarty->assign( "team_member"		, $aoTeamMember );
		
		$smarty->display('Team/TeamDetail.tmpl');
	}

	public function form(){
        self::_displayTeamForm();
	}

	private function _displayTeamForm(){
		$smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
		$smarty->display('Team/form.tmpl');
	}
}
