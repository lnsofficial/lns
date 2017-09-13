<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'TeamOwner.php' );
require_once( PATH_MODEL . 'TeamContact.php' );
require_once( PATH_MODEL . 'TeamMembers.php' );
require_once( PATH_MODEL . 'UserTeamApply.php' );
require_once( PATH_MODEL . 'User.php' );
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
        $team_id = self::insertTeam();
		if( false === $team_id ){
			self::displayError();
			exit;
		}

        $user_id = $this->_user_id_tmp;
        self::insertTeamMember($user_id, $team_id);

        self::insertTeamOwner($team_id);
		
		// 画面表示
		self::_displayCommit($team_id);
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
	
	private function insertTeam(){
        // add team
        $oDb = new Db();
        $oDb->beginTransaction();
        $oTeams = new Teams( $oDb );
        $oTeams->user_id = $this->_user_id_tmp;
        $oTeams->team_name = $_REQUEST["inputTeamNm"];
        $oTeams->team_name_kana = $_REQUEST["inputTeamNmKana"];
        $oTeams->team_tag = $_REQUEST["inputTeamTag"];
        $oTeams->team_tag_kana = $_REQUEST["inputTeamTagKana"];
        $oTeams->save();
        $team_id = $oDb->getLastInsertId();
        $oDb->commit();
    
        if (isset($team_id)) {
            return $team_id;
        }
        return false;
	}

	private function insertTeamOwner($team_id){
        // add owner
        $oDb = new Db();
        $oDb->beginTransaction();
        $oTeamOwner = new TeamOwner( $oDb );
        $oTeamOwner->user_id = $this->_user_id_tmp;
        $oTeamOwner->team_id = $team_id;
        $oTeamOwner->save();
        $oDb->commit();
	}

	private function insertTeamMember($user_id, $team_id){
        // add member
        $oDb = new Db();
        $oDb->beginTransaction();
        $oTeamOwner = new TeamMembers( $oDb );
        $oTeamOwner->user_id = $user_id;
        $oTeamOwner->team_id = $team_id;
        $oTeamOwner->save();
        $oDb->commit();
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

	private function _displayCommit($team_id){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign("inputTeamNm", $_REQUEST["inputTeamNm"]);
		$smarty->assign("inputTeamNmKana", $_REQUEST["inputTeamNmKana"]);
		$smarty->assign("inputTeamTag", $_REQUEST["inputTeamTag"]);
		$smarty->assign("inputTeamTagKana", $_REQUEST["inputTeamTagKana"]);
		$smarty->assign("team_id",          $team_id);
		
		$smarty->display('Team/commit.tmpl');
	}
	
	// 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
	public function displayError(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('TeamRegister_err.tmpl');
	}
	
	public function detail( $team_id = 0 ){
		// get team from user_id
		$user_id = $this->_user_id_tmp;

		$oDb = new Db();

//		$oTeam = Teams::getTeamFromUserId( $user_id );
		$oTeam = Teams::find( $team_id );

		// team members
		$team_members = TeamMembers::getByTeamId( $oTeam["id"] );

		// team owner user_id
		$team_owner   = TeamOwner::getUserIdFromTeamId( $oTeam["id"] );

		// contact user id
		$team_contact = TeamContact::getUserIdFromTeamId( $oTeam["id"] );

		// user_team_applys
		$user_team_applys = UserTeamApply::getByTeamId( $oTeam["id"] );

		// users
		$user = new User( $oDb, $user_id );

		$smarty = new Smarty();

		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign( "login"            , false );

		$smarty->assign( "team_name"		, $oTeam["team_name"] );
		$smarty->assign( "team_name_kana"	, $oTeam["team_name_kana"] );
		$smarty->assign( "team_tag"			, $oTeam["team_tag"] );
		$smarty->assign( "team_tag_kana"	, $oTeam["team_tag_kana"] );

		$smarty->assign( "team_members"     , $team_members );
		$smarty->assign( "team_owner"       , $team_owner );
		$smarty->assign( "team_contact"     , $team_contact );
		$smarty->assign( "user_team_applys" , $user_team_applys );
		$smarty->assign( "user"             , $user );
		$smarty->assign( "team"             , $oTeam );

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



	/**
	 * // [Action]チームへ参加申請するやつ
	 *
	 * @require int                team_id                  // teams.id
	 */
	public function apply()
	{
		// バリデーション（今のとこ必須チェックだけ）
		if( !$_REQUEST["team_id"] )
		{
			self::displayError();
			exit;
		}

//		$user_id = $this->_user_id_tmp;
		$user_id = 124;
		$team_id = $_REQUEST["team_id"];

		// 既にチーム所属済みだったらだめ。
		if( TeamMembers::findByUserId( $user_id ) )
		{
			self::displayError();
			exit;
		}

		// 既にオファー済みだったらだめ。
		if( UserTeamApply::findByUserIdTeamIdState( $user_id, $team_id, UserTeamApply::STATE_APPLY ) )
		{
			self::displayError();
			exit;
		}

		// レコードつくる
		$user_team_apply = UserTeamApply::create([
			'user_id' => $user_id,
			'team_id' => $team_id,
			'type'    => UserTeamApply::TYPE_MEMBER,
			'state'   => UserTeamApply::STATE_APPLY,
		]);
		if( ! $user_team_apply )
		{
			self::displayError();
			exit;
		}

		$smarty = new Smarty();

		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign( "user_team_apply"	, $user_team_apply );

		$smarty->display('Team/apply_complete.tmpl');

	}

}
