<?php
	define( "PATH_BASE"			, getenv('BASE_PATH') );
	define( "PATH_LIB"			, PATH_BASE . "lib/" );
	define( "PATH_CONTROLLER"	, PATH_LIB . "Controller/" );
	define( "PATH_MODEL"		, PATH_LIB . "Model/" );
	define( "PATH_TMP_IMAGE"	, PATH_BASE . "htdocs/tmp/image/" );
	define( "PATH_IMAGE"		, PATH_BASE . "image/" );
	define( "PATH_TMPL"			, PATH_BASE . "tmpl/" );
	define( "PATH_TMPL_C"		, PATH_BASE . "tmpl_c/" );
	define( "PATH_SMARTY"		, PATH_LIB . "smarty-3.1.30/" );
	define( "LIB_SMARTY"		, PATH_SMARTY . "libs/Smarty.class.php" );
	
	
	// DB関連
	define( "DB_NAME"		, getenv('DB_NAME') );
	define( "DB_USER"		, getenv('DB_USER') );
	define( "DB_PASSWORD"	, getenv('DB_PASSWORD') );
	
	// バッチ関連
	define( "INTERVAL_BATCH_TIME"	, "1 WEEK" );
	
	
	// 見出し
	define( "MSG_HEAD_MATCH_COMPLETE"	, "登録が完了しました" );
	define( "MSG_HEAD_MATCH_CANCEL"		, "キャンセルしました" );
	define( "ERR_HEAD_COMMON"		, "エラーが発生しました" );
	
	// メッセージ
	define( "MSG_MATCH_COMPLETE"			, "試合の登録が完了しました" );
	define( "MSG_MATCH_RECRUIT_COMPLETE"	, "試合の募集が完了しました。" );
	define( "MSG_MATCH_RESULT_COMPLETE"		, "試合結果の登録が完了しました" );
	define( "MSG_MATCH_CANCEL"				, "試合のキャンセルが完了しました" );
	define( "ERR_COMMON_INPUT"				, "入力に誤りがあります。再度やり直してください" );
	define( "ERR_MATCH_NOT_RECRUITE"		, "募集中でないマッチです" );
	define( "ERR_MATCH_HOST_EQ_GUEST"		, "自分の募集には登録できません" );
	define( "ERR_MATCH_HOST_DONT_APPLY"		, "募集条件に合致しないため、登録できません" );
	define( "ERR_MATCH_REGIST_INTERVAL"		, "試合を申し込んでから５日間が経過していないため、試合を申し込めません。<br />※ゲスト側で試合申し込みを行った場合、そこから５日間は新たな試合を申し込めません。ただし、募集開始から１日以上経過した試合には申し込むことが可能です。" );
	define( "ERR_MATCH_COMPLETE"			, "試合の登録が失敗しました" );
	define( "ERR_MATCH_PERMISSION"			, "権限がありません" );
	define( "ERR_MATCH_OVER_REGIST"			, "既に該当月での試合の募集回数が上限に達しているため、応募できません。<br />１ヶ月に間に募集できる回数は４回までとなります。" );
	define( "ERR_MATCH_OVER_RESULT_REGIST"	, "試合結果の登録可能時間を過ぎているため、登録できません" );
	define( "ERR_MATCH_WINNER_NOT_PART"		, "勝者が参加チームのどちらでもありません" );
