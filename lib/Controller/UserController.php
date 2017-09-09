<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'User.php' );

class UserController extends BaseController{

	public function form(){
		self::displayUserForm();
	}

	public function displayUserForm(){
		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		$smarty->display('User/form.tmpl');
	}

	public function confirm(){
		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		// IDがダブってないか確認
		if( !self::checkDuplicateLoginId( $_REQUEST["login_id"] ) ){
			self::displayError();
			exit;
		}
		
		// 画面表示
		self::displayConfirm();
	}
	
	public function register(){
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		$oDb = new Db();
		$oDb->beginTransaction();
		
		$oUser = new User( $oDb );
		
		$oUser->login_id = $_REQUEST["login_id"];
		$oUser->password = password_hash( $_REQUEST["password"], CRYPT_SHA256 );
		$oUser->state = 1;
		$oUser->discord_id = $_REQUEST["discord_id"];
		$oUser->summoner_name = null;
		$oUser->summoner_name = $_REQUEST["summoner_name"];
		$oUser->main_role = $_REQUEST["main_role"];
		$oUser->main_champion = $_REQUEST["main_champion"];
		
		$oUser->save();
		var_dump($oUser);
		
		$oDb->commit();
		
		// 画面表示
		self::displayNormal();
	}
	
	private function validation(){
		$bResult	= true;
		if(!$_REQUEST["login_id"]){
			$bResult = false;
		}
		if(!$_REQUEST["password"]){
			$bResult = false;
		}
		if(!$_REQUEST["summoner_name"]){
			$bResult = false;
		}
		if(!$_REQUEST["main_role"]){
			$bResult = false;
		}
		return $bResult;
	}
	
	private function checkDuplicateLoginId( $login_id ){
		$bResult = false;
		$oUser = User::getUserFromLoginId( $login_id );
		
		if( !$oUser ) $bResult = true;
		
		return $bResult;
	}
	

	
	// 正常画面表示
	// TODO 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
	public function displayNormal(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('User/complete.tmpl');
	}
	
	// 確認画面表示
	// TODO その内共通化
	public function displayConfirm(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign("login_id", $_REQUEST["login_id"]);
		$smarty->assign("password", $_REQUEST["password"]);
		$smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
		$smarty->assign("main_role", $_REQUEST["main_role"]);
		$smarty->assign("main_champion", $_REQUEST["main_champion"]);
		$smarty->assign("discord_id", $_REQUEST["discord_id"]);
		
		$smarty->display('User/confirm.tmpl');
	}
	
	// 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
	public function displayError(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('TeamRegister_err.tmpl');
	}

}