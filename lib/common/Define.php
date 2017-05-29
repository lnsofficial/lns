<?php
	//define("PATH_BASE"			, "/var/www/html/");
	define("PATH_BASE"			, getenv('BASE_PATH'));
	define("PATH_LIB"			, PATH_BASE . "lib/");
	define("PATH_CONTROLLER"	, PATH_LIB . "Controller/");
	define("PATH_MODEL"			, PATH_LIB . "Model/");
	define("PATH_TMPL"			, PATH_BASE . "tmpl/");
	define("PATH_TMPL_C"		, PATH_BASE . "tmpl_c/");
	define("PATH_SMARTY"		, PATH_LIB . "smarty-3.1.30/");
	define("LIB_SMARTY"			, PATH_SMARTY . "libs/Smarty.class.php");
	
	
	// DB関連
	define("DB_NAME"		, getenv('DB_NAME') );
	define("DB_USER"		, getenv('DB_USER') );
	define("DB_PASSWORD"	, getenv('DB_PASSWORD') );