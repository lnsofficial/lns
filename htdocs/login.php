<?php
ini_set('display_errors', 1);

require_once('../lib/common/Define.php');
require_once( PATH_LIB . '/common/Db.php');

session_set_save_handler( new MysqlSessionHandler() );

require_unlogined_session();

$sLoginId = $_REQUEST["login_id"];
$sPassword = $_REQUEST["password"];
$oDb = new Db();


// パスワード取得
$sSelectLoginAccountSql = "SELECT account_id, passwd FROM login_account WHERE login_id = ?";
if( $stmt = $oDb->db->prepare( $sSelectLoginAccountSql ) ){
	$stmt->bind_param( "s", $sLoginId );
	$stmt->execute();
	$stmt->store_result();
	
	if( $stmt->error ){
		// TODO エラー処理
		echo $stmt->error;
	}else{
		$stmt->bind_result( $iAccountId, $sPasswordHash );
		$stmt->fetch();
		$stmt->close();
		
		if( password_verify( $sPassword, $sPasswordHash ) ){
			session_regenerate_id(true);
			$_SESSION["id"] = $iAccountId;
			header('location: /Match/RecruitList');
			exit;
		} else {
			header('location: login.html');
			exit;
		}
	}
}