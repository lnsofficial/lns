<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'TeamOwner.php' );
require_once( PATH_MODEL . 'TeamContact.php' );
require_once( PATH_MODEL . 'TeamMembers.php' );
require_once( PATH_MODEL . 'UserTeamApply.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_LIB . '/common/UtilTime.php');
// TODO 最低限の共通化、全コントローラーで共通部分はBaseControllerにまとめる
// 特別に処理を入れる場合のみ、各Controllerに追記する形で開発する

class TeamController extends BaseController{
	const INPUT_DATA = [
		
	];
    // tmp
	public function __construct(){
	}
	
	public function confirm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		// 画面表示
		self::_displayConfirm();
	}
	
	public function register(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

		// self::displayError();
		//TODO 一旦仮で塞ぐ、その内簡単に切り替えれるようにしたい
		// あと、リクエストをInsertメソッドで取ってるとこもその内修正
		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}

        $user_id = $_SESSION["id"];
		
		// DBに登録
        $team_id = self::insertTeam($user_id);
		if( false === $team_id ){
			self::displayError();
			exit;
		}

        self::insertTeamMember($user_id, $team_id);

        self::insertTeamOwner($user_id, $team_id);
		
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
	
	private function insertTeam( $user_id ){
        // add team
        $oDb = new Db();
        $oDb->beginTransaction();
        $oTeams = new Teams( $oDb );
        $oTeams->user_id = $user_id;
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

	private function insertTeamOwner($user_id, $team_id){
        // add owner
        $oDb = new Db();
        $oDb->beginTransaction();
        $oTeamOwner = new TeamOwner( $oDb );
        $oTeamOwner->user_id = $user_id;
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
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

		// get team from user_id
        $user_id = $_SESSION["id"];

		$oDb = new Db();

//		$oTeam = Teams::getTeamFromUserId( $user_id );
//		$oTeam = Teams::find( $team_id );
		$oTeam = new Teams( $oDb, $team_id );

		// team members
		$team_members = TeamMembers::getByTeamId( $oTeam->id );

		// team owner user_id
		$team_owner   = TeamOwner::getUserIdFromTeamId( $oTeam->id );

		// contact user id
		$team_contact = TeamContact::getUserIdFromTeamId( $oTeam->id );

		// user_team_applys
		$user_team_applys = UserTeamApply::getByTeamId( $oTeam->id );
		
		// team_staffs
		$team_staffs = $oTeam->getStaff();

		// users
		$user = new User( $oDb, $user_id );

		$smarty = new Smarty();

		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign( "login"            , false );

		$smarty->assign( "team_id"			, $oTeam->id );
		$smarty->assign( "team_name"		, $oTeam->team_name );
		$smarty->assign( "team_name_kana"	, $oTeam->team_name_kana );
		$smarty->assign( "team_tag"			, $oTeam->team_tag );
		$smarty->assign( "team_tag_kana"	, $oTeam->team_tag_kana );

		$smarty->assign( "team_members"     , $team_members );
		$smarty->assign( "team_owner"       , $team_owner );
		$smarty->assign( "team_contact"     , $team_contact );
		$smarty->assign( "user_team_applys" , $user_team_applys );
		$smarty->assign( "user"             , $user );
		$smarty->assign( "team"             , $oTeam );

		$smarty->display('Team/TeamDetail.tmpl');
	}

	public function form(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

        self::_displayTeamForm();
	}

	private function _displayTeamForm(){
		$smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
		$smarty->display('Team/form.tmpl');
	}

	
	public function searchList(){
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		$smarty->display('Team/TeamSearch.tmpl');
	}


	/**
	 * // [Action]チームへ参加申請するやつ
	 *
	 * @require int                team_id                  // teams.id
	 */
	public function apply()
	{
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

		// バリデーション（今のとこ必須チェックだけ）
		if( !$_REQUEST["team_id"] || !$_REQUEST["type"] )
		{
			self::displayError();
			exit;
		}

        $user_id = $_SESSION["id"];
		$team_id = $_REQUEST["team_id"];
		$type    = $_REQUEST["type"];

		// 既に同職種に申請済みだったらだめ。
		if( UserTeamApply::findByUserIdTeamIdTypeState( $user_id, $team_id, $type, UserTeamApply::STATE_APPLY ) )
		{
			self::displayError();
			exit;
		}

		switch( $type )
		{
			case UserTeamApply::TYPE_MEMBER:
				// いづれかのチームにメンバーとして所属済みならだめ。
				if( TeamMembers::findByUserId( $user_id ) )
				{
					self::displayError();
					exit;
				}
				break;

			case UserTeamApply::TYPE_CONTACT:
				// 他のチームの連絡者であってもだいじょぶだよ
				// このチームの連絡者だったらだめだよ
				if( TeamContact::findByUserIdTeamId( $user_id, $team_id ) )
				{
					self::displayError();
					exit;
				}
				break;

			case UserTeamApply::TYPE_STAFF:
				// 他のチームのアナリストであってもだいじょぶだよ
				// このチームのアナリストだったらだめだよ
				if( TeamStaffs::findByUserIdTeamId( $user_id, $team_id ) )
				{
					self::displayError();
					exit;
				}
				break;

			default:
				self::displayError();
				exit;
		}


		$db = new Db();
		$db->beginTransaction();

		$user_team_apply          = new UserTeamApply( $db );
		$user_team_apply->user_id = $user_id;
		$user_team_apply->team_id = $team_id;
		$user_team_apply->type    = $type;
		$user_team_apply->state   = UserTeamApply::STATE_APPLY;
		$user_team_apply->save();

		$db->commit();



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


	/**
	 * // [Action]チームへの参加申請を承認するやつ
	 *
	 * @require int                user_team_apply_id       // user_team_applys.id
	 */
	public function accept()
	{
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		// バリデーション（今のとこ必須チェックだけ）
		if( !$_REQUEST["user_team_apply_id"] )
		{
			self::displayError();
			exit;
		}

		$user_id = $_SESSION["id"];
		$user_team_apply_id = $_REQUEST["user_team_apply_id"];

		$user = User::info( $user_id );

		///////////////////////////////////////////////////////
		// 適当なuser_team_apply_idじゃないこと
		///////////////////////////////////////////////////////
		$user_team_apply = UserTeamApply::find( $user_team_apply_id );
		if( empty($user_team_apply) )
		{
			self::displayError();
			exit;
		}

		///////////////////////////////////////////////////////
		// 自分がapply先のチームのownerであること
		///////////////////////////////////////////////////////
		if( ! $this->_isOwnerToApply( $user, $user_team_apply ) )
		{
			self::displayError();
			exit;
		}

		///////////////////////////////////////////////////////
		// $user_team_applyがstate == 申請中であること
		///////////////////////////////////////////////////////
		if( $user_team_apply['state'] != UserTeamApply::STATE_APPLY )
		{
			self::displayError();
			exit;
		}


		// 申請内容で処理わけ
		switch( $user_team_apply['type'] )
		{
			// メンバーとしての参加申請の場合
			case UserTeamApply::TYPE_MEMBER:
				// apply出した人がチーム未所属であること
				// applyのtype先に、空きがあること
				$this->_acceptAsMember( $user_team_apply );
				break;

			// 連絡者としての参加申請の場合
			case UserTeamApply::TYPE_CONTACT:
				// このチームの連絡者ではないこと
				// applyのtype先に、空きがあること
				$this->_acceptAsContact( $user_team_apply );
				break;

			// アナリストとしての参加申請の場合
			case UserTeamApply::TYPE_STAFF:
				// このチームのアナリストではないこと
				// applyのtype先に、空きがあること
				$this->_acceptAsStaff( $user_team_apply );
				break;

			default:
				// まぁここに来ることはないでしょう・・
				break;
		}

		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign( "user_team_apply"	, $user_team_apply );

		$smarty->display('Team/apply_accept.tmpl');
	}
	/**
	 * // [SubFunction]オーナーチェック
	 * // 自身がどこかのチームにオーナーとして参加しているかどうか
	 *
	 * @param  array       $user                 // User::info
	 * @param  array       $user_team_apply      // UserTeamApply
	 * @return bool
	 */
	public function _isOwnerToApply( $user, $user_team_apply )
	{
		if( empty($user) || empty($user['team_owners']) )
		{
			return false;
		}
		foreach( $user['team_owners'] as $team_owner )
		{
			if( $team_owner['team_id'] == $user_team_apply['team_id'] )
			{
				return true;
			}
		}
		return false;
	}
	/**
	 * // [SubFunction]メンバーとしての参加申請を承認するやつ
	 *
	 * @param  UserTeamApply      $user_team_apply          // 
	 * @return bool
	 */
	protected function _acceptAsMember( $user_team_apply )
	{
		// apply出した人がチーム未所属であること
		if( TeamMembers::findByUserId( $user_team_apply['user_id'] ) )
		{
			self::displayError();
			exit;
		}

		// メンバー枠に、空きがあること
		$team_members = TeamMembers::getByTeamId( $user_team_apply['team_id'] );
		if( Teams::COUNT_MAX_MEMBER <= count($team_members) )
		{
			self::displayError();
			exit;
		}

		// team_membersにレコード作成してuser_team_applysのstateを更新
		$db = new Db();
		$db->beginTransaction();

		$team_member          = new TeamMembers( $db );
		$team_member->team_id = $user_team_apply['team_id'];
		$team_member->user_id = $user_team_apply['user_id'];
		$team_member->save();

		$apply             = new UserTeamApply( $db, $user_team_apply['id'] );
		$apply->state      = UserTeamApply::STATE_ACCEPT;
		$apply->deleted_at = UtilTime::now();
		$apply->save();

		$db->commit();

		return true;
	}
	/**
	 * // [SubFunction]連絡者としての参加申請を承認するやつ
	 *
	 * @param  UserTeamApply      $user_team_apply          // 
	 * @return bool
	 */
	protected function _acceptAsContact( $user_team_apply )
	{
		// このチームの連絡者ではないこと
		$team_contact = TeamContact::findByUserIdTeamId( $user_team_apply['user_id'], $user_team_apply['team_id'] );
		if( $team_contact )
		{
			self::displayError();
			exit;
		}

		// 連絡者枠に、空きがあること
		$team_contacts = TeamContact::getByTeamId( $user_team_apply['team_id'] );
		if( Teams::COUNT_MAX_CONTACT <= count($team_contacts) )
		{
			self::displayError();
			exit;
		}

		// team_membersにレコード作成してuser_team_applysのstateを更新
		$db = new Db();
		$db->beginTransaction();

		$team_contact          = new TeamContact( $db );
		$team_contact->team_id = $user_team_apply['team_id'];
		$team_contact->user_id = $user_team_apply['user_id'];
		$team_contact->save();

		$apply             = new UserTeamApply( $db, $user_team_apply['id'] );
		$apply->state      = UserTeamApply::STATE_ACCEPT;
		$apply->deleted_at = UtilTime::now();
		$apply->save();

		$db->commit();

		return true;
	}
	/**
	 * // [SubFunction]アナリストとしての参加申請を承認するやつ
	 *
	 * @param  UserTeamApply      $user_team_apply          // 
	 * @return bool
	 */
	protected function _acceptAsStaff( $user_team_apply )
	{
		// このチームのアナリストではないこと
		$team_staff = TeamStaffs::findByUserIdTeamId( $user_team_apply['user_id'], $user_team_apply['team_id'] );
		if( $team_staff )
		{
			self::displayError();
			exit;
		}

		// アナリスト枠に、空きがあること
		$team_staffs = TeamStaffs::getByTeamId( $user_team_apply['team_id'] );
		if( Teams::COUNT_MAX_STAFF <= count($team_staffs) )
		{
			self::displayError();
			exit;
		}

		// team_membersにレコード作成してuser_team_applysのstateを更新
		$db = new Db();
		$db->beginTransaction();

		$team_staff          = new TeamStaffs( $db );
		$team_staff->team_id = $user_team_apply['team_id'];
		$team_staff->user_id = $user_team_apply['user_id'];
		$team_staff->save();

		$apply             = new UserTeamApply( $db, $user_team_apply['id'] );
		$apply->state      = UserTeamApply::STATE_ACCEPT;
		$apply->deleted_at = UtilTime::now();
		$apply->save();

		$db->commit();

		return true;
	}



	/**
	 * // [Action]チームへの参加申請を棄却するやつ
	 *
	 * @require int                user_team_apply_id       // user_team_applys.id
	 */
	public function deny()
	{
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		// バリデーション（今のとこ必須チェックだけ）
		if( !$_REQUEST["user_team_apply_id"] )
		{
			self::displayError();
			exit;
		}

		$user_id = $_SESSION["id"];
		$user_team_apply_id = $_REQUEST["user_team_apply_id"];

		$user = User::info( $user_id );

		///////////////////////////////////////////////////////
		// 適当なuser_team_apply_idじゃないこと
		///////////////////////////////////////////////////////
		$user_team_apply = UserTeamApply::find( $user_team_apply_id );
		if( empty($user_team_apply) )
		{
			self::displayError();
			exit;
		}

		///////////////////////////////////////////////////////
		// 自分がapply先のチームのownerであること
		///////////////////////////////////////////////////////
		if( ! $this->_isOwnerToApply( $user, $user_team_apply ) )
		{
			self::displayError();
			exit;
		}

		///////////////////////////////////////////////////////
		// $user_team_applyがstate == 申請中であること
		///////////////////////////////////////////////////////
		if( $user_team_apply['state'] != UserTeamApply::STATE_APPLY )
		{
			self::displayError();
			exit;
		}


		// 申請内容で処理わけ・・る必要はない。
		// user_team_applysのstateを更新
		$db = new Db();
		$db->beginTransaction();

		$apply             = new UserTeamApply( $db, $user_team_apply['id'] );
		$apply->state      = UserTeamApply::STATE_DENY;
		$apply->deleted_at = UtilTime::now();
		$apply->save();

		$db->commit();


		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign( "user_team_apply"	, $user_team_apply );

		$smarty->display('Team/apply_deny.tmpl');
	}

}

