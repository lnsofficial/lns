<?php
require_once( PATH_MODEL . "Base.php" );

class Team extends Base{
	const TABLE_NAME			= "m_team";
	const COL_ID				= "team_id";
	const COL_LOGIN_ID			= "login_id";
	const COL_PASSWORD			= "password";
	const COL_MAIL_ADDRESS		= "mail_address";
	const COL_TEAM_NAME			= "team_name";
	const COL_TEAM_IMAGE_PATH	= "team_image_path";
	
	// �J����
	const DATA	= [
		[ COL_ID				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	] ],		// �`�[��ID
		[ COL_LOGIN_ID			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	] ],		// ���O�C��ID
		[ COL_PASSWORD			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	] ],		// �p�X���[�h
		[ COL_MAIL_ADDRESS		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	] ],		// ���[���A�h���X
		[ COL_TEAM_NAME			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	] ],		// �`�[����
		[ COL_TEAM_IMAGE_PATH	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	] ],		// �`�[���摜
	];
	
	function __construct(){
		parent::__construct();
	}
	
	// �V�K�쐬
	function register(){
		
	}
	
	// �p�X���[�h����
	
	// ���O�C��
	
}
