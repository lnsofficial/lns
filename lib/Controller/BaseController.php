<?php

require_once( LIB_SMARTY );

class BaseController{
	const INPUT = [];
	const OUTPUT = [];
	
	const DISPLAY_DIR_PATH	= "";
	const DISPLAY_FILE_PATH	= "";

	function __construct(){
	}
	
	public function setParameterFromRequest(){
	}
	
	// 値チェック
	public function validate(){
	}
	
	public function displayCommonScreen( $sHeader, $sMessage ){
		$smarty = new Smarty();
		
		$sTemplateFilePath = static::DISPLAY_DIR_PATH . "/" . static::DISPLAY_FILE_PATH . ".tmpl";
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "header", $sHeader );
		$smarty->assign( "message", $sMessage );
		
		$smarty->display( $sTemplateFilePath );
	}
}
