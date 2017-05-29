<?php

require_once( LIB_SMARTY );

class BaseController{
	const INPUT = [];
	const OUTPUT = [];

	function __construct(){
	}
	
	public function setParameterFromRequest(){
	}
	
	// 値チェック
	public function validate(){
	}
}
