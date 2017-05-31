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
$ahsParameter = [ $sLoginId ];

$oResult = $oDb->executePrepare( $sSelectLoginAccountSql, "s", $ahsParameter );
$iAccountId		= 0;
$sPasswordHash	= "";
while( $row = $oResult->fetch_assoc() ){
	$iAccountId = $row["account_id"];
	$sPasswordHash = $row["passwd"];
	break;
}

if( password_verify( $sPassword, $sPasswordHash ) ){
	session_regenerate_id(true);
	$_SESSION["id"] = $iAccountId;
	header('location: /Match/RecruitList');
	exit;
} else {
	header('location: login.html');
	exit;
}
