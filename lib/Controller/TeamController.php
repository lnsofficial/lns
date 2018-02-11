<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'TeamOwner.php' );
require_once( PATH_MODEL . 'TeamContact.php' );
require_once( PATH_MODEL . 'TeamMembers.php' );
require_once( PATH_MODEL . 'UserTeamApply.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_LIB . '/common/UtilTime.php');
require_once( PATH_MODEL . 'UserRank.php' );
// TODO 最低限の共通化、全コントローラーで共通部分はBaseControllerにまとめる
// 特別に処理を入れる場合のみ、各Controllerに追記する形で開発する
class TeamController extends BaseController{
    const INPUT_DATA = [
        
    ];
    // tmp
    public function __construct(){
    }
    
    public function confirm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        // バリデーション（今のとこ必須チェックだけ）
        if( !self::validation() ){
            self::displayError();
            exit;
        }
        
        // 画面表示
        self::_displayConfirm();
    }
    
    public function editForm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $team_id    = $_REQUEST["team_id"];
        
        $oDb = new Db();
        
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $oTeam = new Teams( $oDb, $team_id );
        
        $authorized = $oTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonError([
                'message' => "権限がありません。",
                'button'   => [
                    'href'      => "/Team/detail/" . $team_id,
                    'name'      => "チーム詳細へ戻る",
                ],
            ]);
            exit;
        }
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("team", $oTeam);
        
        $smarty->display('Team/edit_form.tmpl');
    }
    
    public function editConfirm(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $team_id    = $_REQUEST["team_id"];
        
        $oDb = new Db();
        
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $oTeam = new Teams( $oDb, $team_id );
        
        $authorized = $oTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonError([
                'message' => "権限がありません。",
                'button'   => [
                    'href'      => "/Team/detail/" . $team_id,
                    'name'      => "チーム詳細へ戻る",
                ],
            ]);
            exit;
        }
        
        // バリデーション（今のとこ必須チェックだけ）
        if( !self::validation() ){
            self::displayError();
            exit;
        }
        
        // 画面表示
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("team_id", $team_id);
        $smarty->assign("inputTeamNm", $_REQUEST["inputTeamNm"]);
        $smarty->assign("inputTeamNmKana", $_REQUEST["inputTeamNmKana"]);
        $smarty->assign("inputTeamTag", $_REQUEST["inputTeamTag"]);
        $smarty->assign("inputTeamTagKana", $_REQUEST["inputTeamTagKana"]);
        $smarty->assign("inputComment", $_REQUEST["inputComment"]);
        
        $smarty->display('Team/edit_confirm.tmpl');
    }
    
    public function editComplete(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        // バリデーション（今のとこ必須チェックだけ）
        if( !self::validation() ){
            self::displayError();
            exit;
        }
        
        $team_id    = $_REQUEST["team_id"];
        
        $oDb = new Db();
        
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $oTeam = new Teams( $oDb, $team_id );
        
        $authorized = $oTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonError([
                'message' => "権限がありません。",
                'button'   => [
                    'href'      => "/Team/detail/" . $team_id,
                    'name'      => "チーム詳細へ戻る",
                ],
            ]);
            exit;
        }
        
        $oDb->beginTransaction();
        
        $oTeam->team_name       = $_REQUEST["inputTeamNm"];
        $oTeam->team_name_kana  = $_REQUEST["inputTeamNmKana"];
        $oTeam->team_tag        = $_REQUEST["inputTeamTag"];
        $oTeam->team_tag_kana   = $_REQUEST["inputTeamTagKana"];
        $oTeam->comment         = $_REQUEST["inputComment"];
        
        $oTeam->save();
        $oDb->commit();
        
        // 画面表示
        header('Location: /Team/Detail/' . $team_id);
    }
    
    public function register(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        // self::displayError();
        //TODO 一旦仮で塞ぐ、その内簡単に切り替えれるようにしたい
        // あと、リクエストをInsertメソッドで取ってるとこもその内修正
        // バリデーション（今のとこ必須チェックだけ）
        if( !self::validation() ){
            self::displayError();
            exit;
        }
        // どこかへメンバー申請中は、チーム作成だめ。
        if( UserTeamApply::findByUserIdTypeState( $_SESSION["id"], UserTeamApply::TYPE_MEMBER, UserTeamApply::STATE_APPLY ) )
        {
            self::displayError();
            exit;
        }

        $user_id = $_SESSION["id"];
        
        $oDb = new Db();
        $oDb->beginTransaction();
        
        // DBに登録
        $team_id = self::insertTeam( $oDb, $user_id );
        if( !$team_id ){
            self::displayError();
            exit;
        }
        
        self::insertTeamMember( $oDb, $user_id, $team_id );
        self::insertTeamOwner( $oDb, $user_id, $team_id );
        $oDb->commit();
        
        // 画面表示
        self::_displayCommit($team_id);
    }
    
    // TODO バリデーション処理の実行、とりあえずは必須チェックだけ
    // あとBaseControllerあたりに共通化して置いとく
    private function validation(){
        $bResult	= true;
        if( !$_REQUEST["inputTeamNm"] ){
            $bResult = false;
        }
        if( !$_REQUEST["inputTeamNmKana"] ){
            $bResult = false;
        }
        if( !$_REQUEST["inputTeamTag"] ){
            $bResult = false;
        }
        if( !$_REQUEST["inputTeamTagKana"] ){
            $bResult = false;
        }
        return $bResult;
    }
    
    private function insertTeam( $oDb, $user_id ){
        // add team
        $oTeams = new Teams( $oDb );
        $oTeams->user_id = $user_id;
        $oTeams->team_name = $_REQUEST["inputTeamNm"];
        $oTeams->team_name_kana = $_REQUEST["inputTeamNmKana"];
        $oTeams->team_tag = $_REQUEST["inputTeamTag"];
        $oTeams->team_tag_kana = $_REQUEST["inputTeamTagKana"];
        $oTeams->comment = $_REQUEST["inputComment"];
        $oTeams->save();
    
        return $oTeams->id;
    }
    private function insertTeamOwner($oDb, $user_id, $team_id){
        // add owner
        $oTeamOwner = new TeamOwner( $oDb );
        $oTeamOwner->user_id = $user_id;
        $oTeamOwner->team_id = $team_id;
        $oTeamOwner->save();
    }
    private function insertTeamMember($oDb, $user_id, $team_id){
        // add member
        $oTeamOwner = new TeamMembers( $oDb );
        $oTeamOwner->user_id = $user_id;
        $oTeamOwner->team_id = $team_id;
        $oTeamOwner->save();
    }
    
    // 確認画面表示
    // TODO その内共通化
    private function _displayConfirm(){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("inputTeamNm", $_REQUEST["inputTeamNm"]);
        $smarty->assign("inputTeamNmKana", $_REQUEST["inputTeamNmKana"]);
        $smarty->assign("inputTeamTag", $_REQUEST["inputTeamTag"]);
        $smarty->assign("inputTeamTagKana", $_REQUEST["inputTeamTagKana"]);
        $smarty->assign("inputComment", $_REQUEST["inputComment"]);
        
        $smarty->display('Team/confirm.tmpl');
    }
    private function _displayCommit($team_id){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("inputTeamNm", $_REQUEST["inputTeamNm"]);
        $smarty->assign("inputTeamNmKana", $_REQUEST["inputTeamNmKana"]);
        $smarty->assign("inputTeamTag", $_REQUEST["inputTeamTag"]);
        $smarty->assign("inputTeamTagKana", $_REQUEST["inputTeamTagKana"]);
        $smarty->assign("team_id",          $team_id);
        
        $smarty->display('Team/commit.tmpl');
    }
    
    // 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
    public function displayError(){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->display('TeamRegister_err.tmpl');
    }
    
    /**
     * // [SubFunction]汎用エラー画面だすやつ
     *
     * @param  array                 $param       // []
     */
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
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("error", $error);
        $smarty->display('commonError.tmpl');
    }
    
    public function detail( $team_id = 0 ){
        session_set_save_handler( new MysqlSessionHandler() );
        @session_start();
        
        $oDb = new Db();
        $oTeam = new Teams( $oDb, $team_id );
        if( $oTeam->id == null ){
            self::displayError();
            exit();
        }

        // team members
        $team_members = TeamMembers::getByTeamId( $oTeam->id );
        // team owner user_id
        $team_owner   = TeamOwner::getUserIdFromTeamId( $oTeam->id );
        // contact user id
        $team_contacts = TeamContact::getByTeamId( $oTeam->id );
        // このチームへ届いている申請一覧
        $applys_for_team = UserTeamApply::getByTeamId( $oTeam->id );
        // team logo
        $logo_file = Teams::getLogoFileName( $oTeam->id );
        $logo_path = PATH_TEAM_LOGO . $logo_file;
        if (!file_exists($logo_path)) {
            $logo_path = false;
        }
        
        // team_staffs
        $team_staffs = TeamStaffs::getByTeamId( $oTeam->id );
        // users
        

        // 「大会に参加する」ボタンの表示可否
        $bLogin = false;
        $isThisTeamJoinedLadder = false;
        if( isset( $_SESSION["id"] ) ){
            $bLogin = true;
            $user_id = $_SESSION["id"];
            $user = User::info( $user_id );
            $isThisTeamEnableJoinLadder = false;
            if( $team_owner->id == $user["id"] ){
                $isThisTeamEnableJoinLadder = true;
            }
            
            if( $oTeam->getCurrentLadder( $oDb ) ){
                $isThisTeamJoinedLadder = true;
                $isThisTeamEnableJoinLadder = false;
            }
            // 既に"今期"大会に参加済み( $oTeam->getCurrentLadder( $oDb )==true相当 )だったらここのチェックはしない。
            else
            {
                if( count( $team_members ) )
                {
                    foreach( $team_members as $member )
                    {
                        // 現在ランクと前シーズンランクの情報があるか？
                        if( !isset( $member["summoner_id"] ) || empty( $member["now_rank"] ) || empty( $member["before_rank"] ) )
                        {
                            $isThisTeamEnableJoinLadder = false;
                            break;
                        }
                    }
                }
            }
        }
        
        // 自身のチーム所属情報
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->default_modifiers[] = 'nl2br';
        
        $smarty->assign( "login"            , $bLogin );
        $smarty->assign( "team_members"     , $team_members );
        $smarty->assign( "team_owner"       , $team_owner );
        $smarty->assign( "team_contacts"    , $team_contacts );
        $smarty->assign( "team_staffs"      , $team_staffs );
        $smarty->assign( "applys_for_team"  , $applys_for_team );
        $smarty->assign( "team"             , $oTeam );
        $smarty->assign( "logo_file"        , $logo_file );
        if( isset( $user ) ){
            $smarty->assign( "user"         , $user );
            
            $ahsTeamMemberInfo = $oTeam->getTeamMemberInfoById( $user_id );
            
            $isThisTeamMember       = !empty( $ahsTeamMemberInfo["member"] );
            $isThisTeamContact      = count( array_filter($user['team_contacts'],function($item)use($team_id){ return $item['team_id']==$team_id; }) );
            $isThisTeamStaff        = count( array_filter($user['team_staffs'],  function($item)use($team_id){ return $item['team_id']==$team_id; }) );
            $isTeamMemberApply      = count( array_filter($user['user_team_applys'],function($item)use($team_id){ return $item['type']==UserTeamApply::TYPE_MEMBER; }) );
            $isThisTeamContactApply = count( array_filter($user['user_team_applys'],function($item)use($team_id){ return $item['type']==UserTeamApply::TYPE_CONTACT && $item['team_id']==$team_id; }) );
            $isThisTeamStaffApply   = count( array_filter($user['user_team_applys'],function($item)use($team_id){ return $item['type']==UserTeamApply::TYPE_STAFF   && $item['team_id']==$team_id; }) );
            
            $smarty->assign( "isThisTeamMember"             , $isThisTeamMember );
            $smarty->assign( "isThisTeamContact"            , $isThisTeamContact );
            $smarty->assign( "isThisTeamStaff"              , $isThisTeamStaff );
            $smarty->assign( "isTeamMemberApply"            , $isTeamMemberApply );
            $smarty->assign( "isThisTeamContactApply"       , $isThisTeamContactApply );
            $smarty->assign( "isThisTeamStaffApply"         , $isThisTeamStaffApply );
            $smarty->assign( "isThisTeamEnableJoinLadder"   , $isThisTeamEnableJoinLadder );
            $smarty->assign( "isThisTeamJoinedLadder"       , $isThisTeamJoinedLadder );
        }

        $smarty->display('Team/TeamDetail.tmpl');
    }

    public function form(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

        // どこかへメンバー申請中は、チーム作成だめ。
        if( UserTeamApply::findByUserIdTypeState( $_SESSION["id"], UserTeamApply::TYPE_MEMBER, UserTeamApply::STATE_APPLY ) )
        {
            self::displayError();
            exit;
        }

        self::_displayTeamForm();
    }
    private function _displayTeamForm(){
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->display('Team/form.tmpl');
    }
    
    public function searchList(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        $iUserId = $_SESSION["id"];
        
        $aoTeamList = Teams::getSearchList();
        $oUser = new User( $oDb, $iUserId );
        $oTeam = $oUser->getTeam();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->assign( "team_list" , $aoTeamList );
        $smarty->assign( "team", $oTeam );
        
        $smarty->display('Team/TeamSearch.tmpl');
    }

    /**
     * // [Action]チームへ参加申請するやつ
     *
     * @require int                team_id                  // teams.id
     */
    public function apply()
    {
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        // バリデーション（今のとこ必須チェックだけ）
        if( !$_REQUEST["team_id"] || !$_REQUEST["type"] )
        {
            self::displayError();
            exit;
        }
        $user_id = $_SESSION["id"];
        $team_id = $_REQUEST["team_id"];
        $type    = $_REQUEST["type"];
        // 既に同職種に申請済みだったらだめ。
        if( UserTeamApply::findByUserIdTeamIdTypeState( $user_id, $team_id, $type, UserTeamApply::STATE_APPLY ) )
        {
            self::displayError();
            exit;
        }
        switch( $type )
        {
            case UserTeamApply::TYPE_MEMBER:
                // いづれかのチームにメンバーとして所属済みならだめ。
                if( TeamMembers::findByUserId( $user_id ) )
                {
                    self::displayError();
                    exit;
                }
                // ここ含む、どこかのチームにメンバー申請を既に出していたらだめ。
                if( UserTeamApply::findByUserIdTypeState( $user_id, UserTeamApply::TYPE_MEMBER, UserTeamApply::STATE_APPLY ) )
                {
                    self::displayError();
                    exit;
                }
                break;
            case UserTeamApply::TYPE_CONTACT:
                // 他のチームの連絡者であってもだいじょぶだよ
                // このチームの連絡者だったらだめだよ
                if( TeamContact::findByUserIdTeamId( $user_id, $team_id ) )
                {
                    self::displayError();
                    exit;
                }
                break;
            case UserTeamApply::TYPE_STAFF:
                // 他のチームのアナリストであってもだいじょぶだよ
                // このチームのアナリストだったらだめだよ
                if( TeamStaffs::findByUserIdTeamId( $user_id, $team_id ) )
                {
                    self::displayError();
                    exit;
                }
                break;
            default:
                self::displayError();
                exit;
        }
        $db = new Db();
        $db->beginTransaction();
        $user_team_apply          = new UserTeamApply( $db );
        $user_team_apply->user_id = $user_id;
        $user_team_apply->team_id = $team_id;
        $user_team_apply->type    = $type;
        $user_team_apply->state   = UserTeamApply::STATE_APPLY;
        $user_team_apply->save();
        $db->commit();
        if( ! $user_team_apply )
        {
            self::displayError();
            exit;
        }
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->assign( "user_team_apply"	, $user_team_apply );
        $smarty->display('Team/apply_complete.tmpl');
    }

    /**
     * // [Action]チームへの参加申請を承認するやつ
     *
     * @require int                user_team_apply_id       // user_team_applys.id
     */
    public function accept()
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
        // 自分がapply先のチームのownerであること
        ///////////////////////////////////////////////////////
        if( ! $this->_isOwnerToApply( $user, $user_team_apply ) )
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
        // 申請内容で処理わけ
        switch( $user_team_apply['type'] )
        {
            // メンバーとしての参加申請の場合
            case UserTeamApply::TYPE_MEMBER:
                // apply出した人がチーム未所属であること
                // applyのtype先に、空きがあること
                $this->_acceptAsMember( $user_team_apply );
                break;
            // 連絡者としての参加申請の場合
            case UserTeamApply::TYPE_CONTACT:
                // このチームの連絡者ではないこと
                // applyのtype先に、空きがあること
                $this->_acceptAsContact( $user_team_apply );
                break;
            // アナリストとしての参加申請の場合
            case UserTeamApply::TYPE_STAFF:
                // このチームのアナリストではないこと
                // applyのtype先に、空きがあること
                $this->_acceptAsStaff( $user_team_apply );
                break;
            default:
                // まぁここに来ることはないでしょう・・
                break;
        }
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->assign( "user_team_apply"	, $user_team_apply );
        $smarty->display('Team/apply_accept.tmpl');
    }
    /**
     * // [SubFunction]オーナーチェック
     * // 自身がどこかのチームにオーナーとして参加しているかどうか
     *
     * @param  array       $user                 // User::info
     * @param  array       $user_team_apply      // UserTeamApply
     * @return bool
     */
    public function _isOwnerToApply( $user, $user_team_apply )
    {
        if( empty($user) || empty($user['team_owners']) )
        {
            return false;
        }
        foreach( $user['team_owners'] as $team_owner )
        {
            if( $team_owner['team_id'] == $user_team_apply['team_id'] )
            {
                return true;
            }
        }
        return false;
    }
    /**
     * // [SubFunction]メンバーとしての参加申請を承認するやつ
     *
     * @param  UserTeamApply      $user_team_apply          // 
     * @return bool
     */
    protected function _acceptAsMember( $user_team_apply )
    {
        // apply出した人がチーム未所属であること
        if( TeamMembers::findByUserId( $user_team_apply['user_id'] ) )
        {
            self::displayError();
            exit;
        }
        // メンバー枠に、空きがあること
        $team_members = TeamMembers::getByTeamId( $user_team_apply['team_id'] );
        if( Teams::COUNT_MAX_MEMBER <= count($team_members) )
        {
            self::displayCommonError([
                'message' => "チームメンバー数オーバーです。",
                'button'   => [
                    'href'      => "/Team/detail/" . $user_team_apply['team_id'],
                    'name'      => "チーム詳細へ戻る",
                ],
            ]);
            exit;
        }
        // team_membersにレコード作成してuser_team_applysのstateを更新
        $db = new Db();
        $db->beginTransaction();
        $team_member          = new TeamMembers( $db );
        $team_member->team_id = $user_team_apply['team_id'];
        $team_member->user_id = $user_team_apply['user_id'];
        $team_member->save();
        $apply             = new UserTeamApply( $db, $user_team_apply['id'] );
        $apply->state      = UserTeamApply::STATE_ACCEPT;
        $apply->deleted_at = UtilTime::now();
        $apply->save();
        $db->commit();
        return true;
    }
    /**
     * // [SubFunction]連絡者としての参加申請を承認するやつ
     *
     * @param  UserTeamApply      $user_team_apply          // 
     * @return bool
     */
    protected function _acceptAsContact( $user_team_apply )
    {
        // このチームの連絡者ではないこと
        $team_contact = TeamContact::findByUserIdTeamId( $user_team_apply['user_id'], $user_team_apply['team_id'] );
        if( $team_contact )
        {
            self::displayError();
            exit;
        }
        // 連絡者枠に、空きがあること
        $team_contacts = TeamContact::getByTeamId( $user_team_apply['team_id'] );
        if( Teams::COUNT_MAX_CONTACT <= count($team_contacts) )
        {
            self::displayError();
            exit;
        }
        // team_membersにレコード作成してuser_team_applysのstateを更新
        $db = new Db();
        $db->beginTransaction();
        $team_contact          = new TeamContact( $db );
        $team_contact->team_id = $user_team_apply['team_id'];
        $team_contact->user_id = $user_team_apply['user_id'];
        $team_contact->save();
        $apply             = new UserTeamApply( $db, $user_team_apply['id'] );
        $apply->state      = UserTeamApply::STATE_ACCEPT;
        $apply->deleted_at = UtilTime::now();
        $apply->save();
        $db->commit();
        return true;
    }
    /**
     * // [SubFunction]アナリストとしての参加申請を承認するやつ
     *
     * @param  UserTeamApply      $user_team_apply          // 
     * @return bool
     */
    protected function _acceptAsStaff( $user_team_apply )
    {
        // このチームのアナリストではないこと
        $team_staff = TeamStaffs::findByUserIdTeamId( $user_team_apply['user_id'], $user_team_apply['team_id'] );
        if( $team_staff )
        {
            self::displayError();
            exit;
        }
        // アナリスト枠に、空きがあること
        $team_staffs = TeamStaffs::getByTeamId( $user_team_apply['team_id'] );
        if( Teams::COUNT_MAX_STAFF <= count($team_staffs) )
        {
            self::displayError();
            exit;
        }
        // team_membersにレコード作成してuser_team_applysのstateを更新
        $db = new Db();
        $db->beginTransaction();
        $team_staff          = new TeamStaffs( $db );
        $team_staff->team_id = $user_team_apply['team_id'];
        $team_staff->user_id = $user_team_apply['user_id'];
        $team_staff->save();
        $apply             = new UserTeamApply( $db, $user_team_apply['id'] );
        $apply->state      = UserTeamApply::STATE_ACCEPT;
        $apply->deleted_at = UtilTime::now();
        $apply->save();
        $db->commit();
        return true;
    }
    /**
     * // [Action]チームへの参加申請を棄却するやつ
     *
     * @require int                user_team_apply_id       // user_team_applys.id
     */
    public function deny()
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
        // 自分がapply先のチームのownerであること
        ///////////////////////////////////////////////////////
        if( ! $this->_isOwnerToApply( $user, $user_team_apply ) )
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
        $apply->state      = UserTeamApply::STATE_DENY;
        $apply->deleted_at = UtilTime::now();
        $apply->save();
        $db->commit();
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->assign( "user_team_apply"	, $user_team_apply );
        $smarty->display('Team/apply_deny.tmpl');
    }
    
    public function leave(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        // バリデーション（今のとこ必須チェックだけ）
        if( !$_REQUEST["team_id"] || !$_REQUEST["type"] ){
            self::displayError();
            exit;
        }
        $user_id = $_SESSION["id"];
        $team_id = $_REQUEST["team_id"];
        $type    = $_REQUEST["type"];
        
        $oDb = new Db();
        $oDb->beginTransaction();
        
        $oTeam = new Teams( $oDb, $team_id );
        if( $oTeam->id == null ){
            self::displayError();
            exit();
        }
        
        $ahsTeamMemberInfo = $oTeam->getTeamMemberInfoById( $user_id );
        if( empty( $ahsTeamMemberInfo ) ){
            // メンバーでなければエラー
            self::displayError();
            exit;
        }
        
        switch( $type ){
            case UserTeamApply::TYPE_MEMBER:
                // メンバー
                // 脱退申請した職種でなければエラー
                if( empty( $ahsTeamMemberInfo["member"] ) ){
                    self::displayError();
                    exit;
                }
                
                // 代表だったら脱退不可
                if( !empty( $ahsTeamMemberInfo["owner"] ) ){
                    self::displayError();
                    exit;
                }
                
                $ahsTeamMemberInfo["member"]->delete();
                break;
            case UserTeamApply::TYPE_CONTACT:
                // 連絡者
                if( empty( $ahsTeamMemberInfo["contact"] ) ){
                    self::displayError();
                    exit;
                }
                
                $ahsTeamMemberInfo["contact"]->delete();
                break;
            case UserTeamApply::TYPE_STAFF:
                // アナリスト
                if( empty( $ahsTeamMemberInfo["staff"] ) ){
                    self::displayError();
                    exit;
                }
                
                $ahsTeamMemberInfo["staff"]->delete();
                break;
            default:
                // それ以外はエラー
                self::displayError();
                exit;
                break;
        }
        
        $oDb->commit();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        $smarty->assign( "team_id"  , $team_id );
        $smarty->assign( "type"     , $type );
        $smarty->display('Team/TeamLeave_complete.tmpl');
    }

    public function uploadTeamLogo()
    {
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

        // check team id
        $team_id = $_REQUEST["team_id"];
        if (empty($team_id)) {
            self::displayError();
            exit;
        }

        // check is owner
        $user_id = $_SESSION["id"];
        $team_owner = TeamOwner::getUserIdFromTeamId( $team_id );
        if (empty($team_owner) || $user_id != $team_owner->id) {
            self::displayError();
            exit;
        }

        // check uploaded file
        if (!isset($_FILES["inputTeamLogo"])) {
            self::displayError();
            exit;
        }
        if ($_FILES["inputTeamLogo"]["size"] == 0) {
            self::displayError();
            exit;
        }
        
        $logo_file = $team_id . "_logo.jpg";
        $logo_path = PATH_TEAM_LOGO . $logo_file;
        move_uploaded_file($_FILES['inputTeamLogo']['tmp_name'], $logo_path);
        
        $this->_displayUploaded($logo_file);
    }

    private function _displayUploaded($logo_file){
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign( "logo_file" , $logo_file);
        $smarty->display('Team/LogoUploaded.tmpl');
    }
}
