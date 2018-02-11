<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_MODEL . 'UserTeamApply.php' );
require_once( PATH_MODEL . 'ApiQueues.php' );
require_once( PATH_LIB . '/common/UtilTime.php');
require_once( PATH_RIOTAPI . 'SummonersByName.php' );
require_once( PATH_MODEL . 'UserPasswordApply.php' );

class UserController extends BaseController{
    const DISPLAY_DIR_PATH    = "Match";
    const DISPLAY_FILE_PATH    = "Match_common";

    public function displayUserForm(){
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->display('User/form.tmpl');
    }

    public function displayBlockUserForm() {
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->display('User/block_form.tmpl');
    }
    
    public function editForm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        $oUser = new User( $oDb, $_SESSION["id"] );
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("summoner_name", $oUser->summoner_name );
        $smarty->assign("summoner_name_kana", $oUser->summoner_name_kana );
        $smarty->assign("main_role", $oUser->main_role );
        $smarty->assign("discord_id", $oUser->discord_id );
        $smarty->assign("comment", $oUser->comment );
        
        $smarty->display('User/edit_form.tmpl');
    }
    
    public function editConfirm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $sErrorMessage = "";
        
        // TODO 共通化
        if( !$_REQUEST["discord_id"] ){
            $sErrorMessage = "DiscordIDが空です";
        }
        if( !$_REQUEST["summoner_name"] ){
            $sErrorMessage = "サモナー名が空です";
        }
        if( !$_REQUEST["summoner_name_kana"] ){
            $sErrorMessage = "サモナー名（かな）が空です";
        }
        
        // DiscordIDの重複チェック
        $oUser = User::getUserFromDiscordId( $_REQUEST["discord_id"] );
        if( $oUser ){
            if( $oUser->id != $oLoginUser->id ){
                $sErrorMessage = "そのDiscordIDは既に利用されています";
            }
        }
        
        // サモネの重複チェック
        $oUser = User::getUserFromSummonerName( $_REQUEST["summoner_name"] );
        if( $oUser ){
            if( $oUser->id != $oLoginUser->id ){
                $sErrorMessage = "そのサモナーネームは既に利用されています";
            }
        }
        
        // 画面表示
        if( $sErrorMessage ){
            $smarty = new Smarty();
            $smarty->template_dir = PATH_TMPL;
            $smarty->compile_dir  = PATH_TMPL_C;
            $smarty->default_modifiers[] = 'escape:html';
            
            $smarty->assign("error_message", $sErrorMessage);
            $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
            $smarty->assign("summoner_name_kana", $_REQUEST["summoner_name_kana"]);
            $smarty->assign("main_role", $_REQUEST["main_role"]);
            $smarty->assign("discord_id", $_REQUEST["discord_id"]);
            $smarty->assign("comment", $_REQUEST["comment"]);
            
            $smarty->display('User/edit_form.tmpl');
        } else {
            self::displayEditConfirm();
        }
    }
    
    public function edit(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $sErrorMessage = "";
        
        // TODO 共通化
        if( !$_REQUEST["discord_id"] ){
            self::displayError();
            exit;
        }
        if( !$_REQUEST["summoner_name"] ){
            self::displayError();
            exit;
        }
        if( !$_REQUEST["summoner_name_kana"] ){
            self::displayError();
            exit;
        }
        
        // DiscordIDの重複チェック
        $oUser = User::getUserFromDiscordId( $_REQUEST["discord_id"] );
        if( $oUser ){
            if( $oUser->id != $oLoginUser->id ){
                self::displayError();
                exit;
            }
        }
        
        // サモネの重複チェック
        $oUser = User::getUserFromSummonerName( $_REQUEST["summoner_name"] );
        if( $oUser ){
            if( $oUser->id != $oLoginUser->id ){
                self::displayError();
                exit;
            }
        }
        
        // 画面表示
        if( $sErrorMessage ){
            $smarty = new Smarty();
            $smarty->template_dir = PATH_TMPL;
            $smarty->compile_dir  = PATH_TMPL_C;
            $smarty->default_modifiers[] = 'escape:html';
            
            $smarty->assign("error_message", $sErrorMessage);
            $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
            $smarty->assign("summoner_name_kana", $_REQUEST["summoner_name_kana"]);
            $smarty->assign("main_role", $_REQUEST["main_role"]);
            $smarty->assign("discord_id", $_REQUEST["discord_id"]);
            $smarty->assign("comment", $_REQUEST["comment"]);
            
            $smarty->display('User/edit_form.tmpl');
        } else {
            $oDb->beginTransaction();
            
            if( $oLoginUser->summoner_name !== $_REQUEST["summoner_name"] ){
                $oApiQueues = new ApiQueues( $oDb );
                
                $oApiQueues->action  = $oLoginUser->summoner_id > 0 ? ApiQueues::ACTION_UPDATE_SUMMONER : ApiQueues::ACTION_REGISTER_SUMMONER;
                $oApiQueues->state   = ApiQueues::STATE_UNTREATED;
                $oApiQueues->payload = json_encode( [ "user_id" => $oLoginUser->id ] );
                $oApiQueues->save();
            }
            
            $oLoginUser->summoner_name = $_REQUEST["summoner_name"];
            $oLoginUser->summoner_name_kana = $_REQUEST["summoner_name_kana"];
            $oLoginUser->main_role = $_REQUEST["main_role"];
            $oLoginUser->discord_id = $_REQUEST["discord_id"];
            $oLoginUser->comment = $_REQUEST["comment"];
            $oLoginUser->save();
            
            $oDb->commit();
            
            exit();
            header('Location: /User/MyPage');
        }
    }
    
    public function display(){
        $iUserId = $_REQUEST["user_id"];
        
        $oDb = new Db();
        $oUser = new User( $oDb, $iUserId );
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("summoner_name", $oUser->summoner_name );
        $smarty->assign("main_role", $oUser->main_role );
        $smarty->assign("discord_id", $oUser->discord_id );
        
        $smarty->display('User/display.tmpl');
    }
    
    public function loginForm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->display('User/login.tmpl');
    }
    
    public function login(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
        $sLoginId = $_REQUEST["login_id"];
        $sPassword = $_REQUEST["password"];
        $oDb = new Db();
        
        $oUser = User::getUserFromLoginId( $sLoginId );
        
        if( $oUser && password_verify( $sPassword, $oUser->password ) ){
            session_regenerate_id(true);
            $_SESSION["id"] = $oUser->id;
            // ログイン後の最初の画面どこにする？
            header('location: /User/MyPage' );
            exit;
        } else {
            $smarty = new Smarty();
            $smarty->template_dir = PATH_TMPL;
            $smarty->compile_dir  = PATH_TMPL_C;
            $smarty->default_modifiers[] = 'escape:html';
            
            $smarty->assign("error_message", "ログインID/パスワードが一致しません");
            
            $smarty->display('User/login.tmpl');
            exit;
        }
    }
    
    public function logout(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        $iUserId = $_SESSION["id"];
        
        setcookie(session_name(), '', 1);
        session_destroy();
        
        header('location: /index.html' );
    }
    
    public function myPage(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        $user_id = $_SESSION["id"];

        $oDb = new Db();
        $oUser = new User( $oDb, $user_id );
        
        $sErrorMessage = null;
        if( !isset( $oUser->summoner_id ) ){
            $oApiQueue = $oUser->getLastApiQueue();
            if( $oApiQueue && $oApiQueue->state == ApiQueues::STATE_FAILED ){
                $sErrorMessage = "サモナーネームの確認に失敗しました、正しいサモナーネームが登録されているか確認してください";
            }
        }

        $user             = User::info( $user_id );                 // ユーザー情報
        $user_team_applys = UserTeamApply::getByUserId( $user_id ); // チームへの申請情報まわり
        
        $oTeam = $oUser->getTeam();

        $smarty = new Smarty();

        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';

        $smarty->assign("user", $oUser );
        $smarty->assign("team", $oTeam );
        $smarty->assign("error_message", $sErrorMessage );

        $smarty->assign("user_team_applys", $user_team_applys );

        $smarty->display('User/mypage.tmpl');
    }

    public function form(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
        if ( BLOCK_USER_REGISTER ) {
            self::displayBlockUserForm();
            exit;
        }

        self::displayUserForm();
    }

    public function confirm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();

        if ( BLOCK_USER_REGISTER ) {
            self::displayBlockUserForm();
            exit;
        }
        
        $sErrorMessage = "";
        // バリデーション（今のとこ必須チェックだけ）
        if( !self::checkRequire() ){
            $sErrorMessage = "必須項目が未入力です";
        }
        
        // ユーザーIDの重複チェック
        if( User::getUserFromLoginId( $_REQUEST["login_id"] ) ){
            $sErrorMessage = "そのログインIDは既に利用されています";
        }
        
        // DiscordIDの重複チェック
        if( User::getUserFromDiscordId( $_REQUEST["discord_id"] ) ){
            $sErrorMessage = "そのDiscordIDは既に利用されています";
        }
        
        // サモネの重複チェック
        if( User::getUserFromSummonerName( $_REQUEST["summoner_name"] ) ){
            $sErrorMessage = "そのサモナーネームは既に利用されています";
        }
        
        // 画面表示
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("login_id", $_REQUEST["login_id"]);
        $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
        $smarty->assign("summoner_name_kana", $_REQUEST["summoner_name_kana"]);
        $smarty->assign("main_role", $_REQUEST["main_role"]);
        //$smarty->assign("main_champion", $_REQUEST["main_champion"]);
        $smarty->assign("discord_id", $_REQUEST["discord_id"]);
        $smarty->assign("comment", $_REQUEST["comment"]);
        
        if( $sErrorMessage ){
            $smarty->assign("error_message", $sErrorMessage);
            $smarty->display('User/form.tmpl');
        } else {
            $smarty->assign("password", $_REQUEST["password"]);
            $smarty->display('User/confirm.tmpl');
        }
        
    }
    
    public function register(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();

        if ( BLOCK_USER_REGISTER ) {
            self::displayBlockUserForm();
            exit;
        }
        
        if( !self::checkRequire() ){
            self::displayError();
            exit;
        }
        
        // ユーザーIDの重複チェック
        if( User::getUserFromLoginId( $_REQUEST["login_id"] ) ){
            self::displayError();
            exit;
        }
        
        // DiscordIDの重複チェック
        if( User::getUserFromDiscordId( $_REQUEST["discord_id"] ) ){
            self::displayError();
            exit;
        }
        
        // サモネの重複チェック
        if( User::getUserFromSummonerName( $_REQUEST["summoner_name"] ) ){
            self::displayError();
            exit;
        }
        
        $oDb = new Db();
        $oDb->beginTransaction();
        
        $oUser = new User( $oDb );
        
        $oUser->login_id            = $_REQUEST["login_id"];
        $oUser->password            = password_hash( $_REQUEST["password"], CRYPT_SHA256 );
        $oUser->state               = 1;
        $oUser->discord_id          = $_REQUEST["discord_id"];
        $oUser->summoner_name       = null;                             // TODO ここ何でnull入れてんだっけ？
        $oUser->summoner_name       = $_REQUEST["summoner_name"];
        $oUser->summoner_name_kana  = $_REQUEST["summoner_name_kana"];
        $oUser->main_role           = $_REQUEST["main_role"];
        $oUser->comment             = $_REQUEST["comment"];
        
        $oUser->save();
        
        $oApiQueues = new ApiQueues( $oDb );
        
        $oApiQueues->action  = ApiQueues::ACTION_REGISTER_SUMMONER;
        $oApiQueues->state   = ApiQueues::STATE_UNTREATED;
        $oApiQueues->payload = json_encode( [ "user_id" => $oUser->id ] );
        
        $oApiQueues->save();
        
        $oDb->commit();
        
        // 画面表示
        self::displayNormal();
    }
    
    // 必須項目チェック
    private function checkRequire(){
        $bResult = true;
        if( empty( $_REQUEST["login_id"] ) ){
            $bResult = false;
        }
        if( empty( $_REQUEST["password"] ) ){
            $bResult = false;
        }
        if( empty( $_REQUEST["summoner_name"] ) ){
            $bResult = false;
        }
        if( empty( $_REQUEST["main_role"] ) ){
            $bResult = false;
        }
        if( empty( $_REQUEST["discord_id"] ) ){
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
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("login_id", $_REQUEST["login_id"]);
        $smarty->assign("password", $_REQUEST["password"]);
        $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
        $smarty->assign("summoner_name_kana", $_REQUEST["summoner_name_kana"]);
        $smarty->assign("main_role", $_REQUEST["main_role"]);
        //$smarty->assign("main_champion", $_REQUEST["main_champion"]);
        $smarty->assign("discord_id", $_REQUEST["discord_id"]);
        $smarty->assign("comment", $_REQUEST["comment"]);
        
        $smarty->display('User/confirm.tmpl');
    }
    
    // 確認画面表示
    public function displayEditConfirm(){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
        $smarty->assign("summoner_name_kana", $_REQUEST["summoner_name_kana"]);
        $smarty->assign("main_role", $_REQUEST["main_role"]);
        $smarty->assign("discord_id", $_REQUEST["discord_id"]);
        $smarty->assign("comment", $_REQUEST["comment"]);
        
        $smarty->display('User/edit_confirm.tmpl');
    }
    
    // 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
    public function displayError(){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->display('TeamRegister_err.tmpl');
    }



    /**
     * // [Action]チームへの参加申請をキャンセルするやつ
     *
     * @require int                user_team_apply_id       // user_team_applys.id
     */
    public function apply_cancel()
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
        // 自分が出したapplyであること
        ///////////////////////////////////////////////////////
        if( $user_team_apply['user_id'] != $user_id )
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
        $apply->state      = UserTeamApply::STATE_CANCEL;
        $apply->deleted_at = UtilTime::now();
        $apply->save();

        $db->commit();


        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';

        $smarty->assign( "user_team_apply" , $user_team_apply );

        $smarty->display('User/apply_cancel.tmpl');
    }


    /**
     * // [Action]指定ユーザーのページを表示
     *
     * @param  int                $user_id       // users.id
     */
    public function show_user( $user_id )
    {
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

        $other = User::info( $user_id );        // 指定ユーザーの情報
        $user  = User::info( $_SESSION["id"] ); // 自分自身の情報

        $smarty               = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';

        $smarty->assign( "other", $other );
        $smarty->assign( "user" , $user );

        $smarty->display('User/show_user.tmpl');
    }

    public function PasswordResetForm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->display('User/password_reset.tmpl');
    }
    
    public function PasswordResetRequest(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
        $oDb = new Db();
        $status = ApiCallStatus::getStatus($oDb, RIOTAPIKEY);
        $check  = $status->checkLimit( false );
        if( !$check['enable'] ){
            // レートリミット引っかかってたらエラー
            self::displayCommonError([
                'message' => "APIの実効制限に引っかかりました。<br>カップ麺を食い終わった頃にもう一度試してください。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        $summoner_name = $_REQUEST["summoner_name"];
        
        // 指定したサモナー名のユーザーが居るか確認
        $oUser = User::getUserFromSummonerName($summoner_name);
        
        if( empty( $oUser ) ){
            self::displayCommonError([
                'message' => "指定したサモナー名は存在しません。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        $api = new SummonersByName();
        $api->setParams( [ "name" => $summoner_name ] );
        
        $json = $api->execApi();
        
        if( empty( $json) ){
            self::displayCommonError([
                'message' => "API実行時にエラーが発生しました。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        $oDb = new Db();
        $oDb->beginTransaction();
        
        $oUserPasswordApply = new UserPasswordApply($oDb);
        $oUserPasswordApply->user_id = $oUser->id;
        $oUserPasswordApply->icon_id = $json["profileIconId"];
        $oUserPasswordApply->code    = hash( "sha256", time() );
        $oUserPasswordApply->state   = userPasswordAPply::STATE_APPLY;
        
        $oUserPasswordApply->save();
        
        $oDb->commit();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign( "id"   , $oUser->id );
        $smarty->assign( "code" , $oUserPasswordApply->code );
        
        $smarty->display('User/password_request.tmpl');
    }
    
    public function PasswordReset(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
        // TODO APIキーの制限引っかかってたらエラー
        $oDb = new Db();
        $status = ApiCallStatus::getStatus($oDb, RIOTAPIKEY);
        $check  = $status->checkLimit( false );
        if( !$check['enable'] ){
            // レートリミット引っかかってたらエラー
            self::displayCommonError([
                'message' => "APIの実効制限に引っかかりました。<br>カップ麺を食い終わった頃にもう一度試してください。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        $id = $_REQUEST["id"];
        $code = $_REQUEST["code"];
        
        
        // サモナー取得
        $oDb->beginTransaction();
        $oUser = new User( $oDb, $id );
        
        if( empty( $oUser ) ){
            self::displayCommonError([
                'message' => "パスワード再申請でエラーが発生しました。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        // パスワード申請取得
        $oUserPasswordApply = UserPasswordApply::getUserPasswordApplyByUserIdCode( $oDb, $id, $code );
        
        if( empty( $oUserPasswordApply ) ){
            self::displayCommonError([
                'message' => "パスワード再申請でエラーが発生しました。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        if( date( 'Y-m-d H:i:s', strtotime( $oUserPasswordApply->updated_at . " + " . PASSWORD_RESET_PERIOD ) ) < date( 'Y-m-d H:i:s' ) ){
            self::displayCommonError([
                'message' => "パスワード再設定可能時間が過ぎています。<br>最初からやり直してください",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        $api = new SummonersByName();
        $api->setParams( [ "name" => $oUser->summoner_name ] );
        
        $json = $api->execApi();
        
        if( empty( $json ) ){
            self::displayCommonError([
                'message' => "API実行時にエラーが発生しました。",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        if( $json["profileIconId"] == $oUserPasswordApply->icon_id ){
            self::displayCommonError([
                'message' => "アイコンが変更されていません。<br>もう一度やり直してください",
                'button'   => [
                    'href'      => "/User/PasswordResetForm" ,
                    'name'      => "パスワード再設定画面へ戻る",
                ],
            ]);
            exit();
        }
        
        $oUserPasswordApply->state   = userPasswordAPply::STATE_SUCCESS;
        
        $oUserPasswordApply->save();
        
        $oDb->commit();
        
        // ログイン
        session_regenerate_id(true);
        $_SESSION["id"] = $oUser->id;
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->display('User/password_form.tmpl');
    }
    
    public function editPassword(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        $oUser = new User( $oDb, $_SESSION["id"] );
        
        
        $password = $_REQUEST["password"];
        $oUser->password = password_hash( $password, CRYPT_SHA256 );
        $oUser->save();
        
        $oDb->commit();
        
        header('Location: /User/MyPage');
    }
    
    public function editPasswordForm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->display('User/password_form.tmpl');
    }

    // TODO TeamControllerから流用、その内BaseControllerに移植
    public function displayCommonError( $param = [] )
    {
        $param_org = [
            'title'   => "エラーが発生しました。",
            'message' => "もう一度やり直してください。",
            'button'   => [
                'href'      => "/index.html",
                'name'      => "戻る",
            ],
        ];
        $error = array_merge( $param_org, $param );
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign("error", $error);
        $smarty->display('commonError.tmpl');
    }

}
