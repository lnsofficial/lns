<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_MODEL . 'UserTeamApply.php' );
require_once( PATH_MODEL . 'ApiQueues.php' );
require_once( PATH_LIB . '/common/UtilTime.php');

class UserController extends BaseController{

    public function displayUserForm(){
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->display('User/form.tmpl');
    }
    
    public function editForm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        $oUser = new User( $oDb, $_SESSION["id"] );
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign("summoner_name", $oUser->summoner_name );
        $smarty->assign("main_role", $oUser->main_role );
        $smarty->assign("discord_id", $oUser->discord_id );
        
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
            
            $smarty->assign("error_message", $sErrorMessage);
            $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
            $smarty->assign("main_role", $_REQUEST["main_role"]);
            $smarty->assign("discord_id", $_REQUEST["discord_id"]);
            
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
            
            $smarty->assign("error_message", $sErrorMessage);
            $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
            $smarty->assign("main_role", $_REQUEST["main_role"]);
            $smarty->assign("discord_id", $_REQUEST["discord_id"]);
            
            $smarty->display('User/edit_form.tmpl');
        } else {
            $oDb->beginTransaction();
            
            if( $oLoginUser->summoner_name !== $_REQUEST["summoner_name"] ){
                $oApiQueues = new ApiQueues( $oDb );
                
                $oApiQueues->action  = isset( $oLoginUser->summoner_id ) ? ApiQueues::ACTION_UPDATE_SUMMONER : ApiQueues::ACTION_REGISTER_SUMMONER;
                $oApiQueues->state   = ApiQueues::STATE_UNTREATED;
                $oApiQueues->payload = json_encode( [ "user_id" => $oLoginUser->id ] );
                $oApiQueues->save();
            }
            
            $oLoginUser->summoner_name = $_REQUEST["summoner_name"];
            $oLoginUser->main_role = $_REQUEST["main_role"];
            $oLoginUser->discord_id = $_REQUEST["discord_id"];
            $oLoginUser->save();
            
            $oDb->commit();
            
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

        $smarty->assign("user", $oUser );
        $smarty->assign("team", $oTeam );
        $smarty->assign("error_message", $sErrorMessage );

        $smarty->assign("user_team_applys", $user_team_applys );

        $smarty->display('User/mypage.tmpl');
    }

    public function form(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        self::displayUserForm();
    }

    public function confirm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
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
        if( $sErrorMessage ){
            $smarty = new Smarty();
            $smarty->template_dir = PATH_TMPL;
            $smarty->compile_dir  = PATH_TMPL_C;
            $smarty->assign("error_message", $sErrorMessage);
            $smarty->assign("login_id", $_REQUEST["login_id"]);
            $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
            $smarty->assign("main_role", $_REQUEST["main_role"]);
            //$smarty->assign("main_champion", $_REQUEST["main_champion"]);
            $smarty->assign("discord_id", $_REQUEST["discord_id"]);
            $smarty->display('User/form.tmpl');
        } else {
            self::displayConfirm();
        }
    }
    
    public function register(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_unlogined_session();
        
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
        
        $oUser->login_id      = $_REQUEST["login_id"];
        $oUser->password      = password_hash( $_REQUEST["password"], CRYPT_SHA256 );
        $oUser->state         = 1;
        $oUser->discord_id    = $_REQUEST["discord_id"];
        $oUser->summoner_name = null;
        $oUser->summoner_name = $_REQUEST["summoner_name"];
        $oUser->main_role     = $_REQUEST["main_role"];
        
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
        
        $smarty->assign("login_id", $_REQUEST["login_id"]);
        $smarty->assign("password", $_REQUEST["password"]);
        $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
        $smarty->assign("main_role", $_REQUEST["main_role"]);
        //$smarty->assign("main_champion", $_REQUEST["main_champion"]);
        $smarty->assign("discord_id", $_REQUEST["discord_id"]);
        
        $smarty->display('User/confirm.tmpl');
    }
    
    // 確認画面表示
    public function displayEditConfirm(){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign("summoner_name", $_REQUEST["summoner_name"]);
        $smarty->assign("main_role", $_REQUEST["main_role"]);
        $smarty->assign("discord_id", $_REQUEST["discord_id"]);
        
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

        $smarty->assign( "other", $other );
        $smarty->assign( "user" , $user );

        $smarty->display('User/show_user.tmpl');
    }



}