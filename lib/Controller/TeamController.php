<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Team.php' );
require_once( PATH_LIB . '/common/Db.php');
// TODO 最低限の共通化、全コントローラーで共通部分はBaseControllerにまとめる
// 特別に処理を入れる場合のみ、各Controllerに追記する形で開発する

class TeamController extends BaseController{
	const INPUT_DATA = [
		
	];
	public function __construct(){
	}
	
	public function confirm(){
		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		// 画面表示
		self::displayConfirm();
	}
	
	public function register(){
		// self::displayError();
		//TODO 一旦仮で塞ぐ、その内簡単に切り替えれるようにしたい
		// あと、リクエストをInsertメソッドで取ってるとこもその内修正
		// バリデーション（今のとこ必須チェックだけ）
		if( !self::validation() ){
			self::displayError();
			exit;
		}
		
		// DBに登録
		if( !self::insert() ){
			self::displayError();
			exit;
		}
		
		// メール送信
		if( !self::sendPreRegisterMail() ){
			self::displayError();
			exit;
		}
		
		// 画面表示
		self::displayNormal();
	}
	
	// TODO バリデーション処理の実行、とりあえずは必須チェックだけ
	// あとBaseControllerあたりに共通化して置いとく
	private function validation(){
		$bResult	= true;
		if( !$_REQUEST["mail_address"] ){
			$bResult = false;
		}
		if( !$_REQUEST["team_name"] ){
			$bResult = false;
		}
		
		if( !$_REQUEST["member"] ){
			$bResult = false;
		} else {
			$aMember = $_REQUEST["member"];
			$aMember = array_filter($aMember, "strlen");
			if( count($aMember) == 0 ){
				$bResult = false;
			}
		}
		return $bResult;
	}
	
	private function insert(){
	
		try{
			$mysqli	= new mysqli('localhost', DB_USER, DB_PASSWORD, DB_NAME);
			$mysqli->autocommit(False);
			
			if( $mysqli->connect_error ){
				echo $mysqli->connect_error;
				exit();
			}
			
			// チーム登録
			$sInsertTeamSql		= "INSERT INTO m_team(mail_address,team_name,team_name_kana,team_tag,team_tag_kana,comment) VALUE(?,?,?,?,?,?)";
			$iTeamId	= 0;
			$mail_address = "";
			
			if($stmt = $mysqli->prepare($sInsertTeamSql)){
				$mail_address	= $_REQUEST["mail_address"];
				$team_name		= $_REQUEST["team_name"];
				$team_name_kana	= $_REQUEST["team_name_kana"];
				$team_tag		= $_REQUEST["team_tag"];
				$team_tag_kana	= $_REQUEST["team_tag_kana"];
				$comment		= $_REQUEST["comment"];
				$stmt->bind_param("ssssss",$mail_address,$team_name,$team_name_kana,$team_tag,$team_tag_kana,$comment);
				$stmt->execute();
				
				if( $stmt->error ){
					// TODO エラー処理
					echo $stmt->error;
				}else{
					$iTeamId	= $mysqli->insert_id;
				}
				
				$stmt->close();
			}
			
			$aMember = $_REQUEST["member"];
			$aMember = array_filter($aMember, "strlen");
			// メンバー登録
			$sInsertMemberSql = "INSERT INTO m_member(team_id,summoner_name) VALUE(?,?)";
			foreach($aMember as $sMember){
				if($stmt = $mysqli->prepare($sInsertMemberSql)){
					$stmt->bind_param("is",$iTeamId,$sMember);
					$stmt->execute();
					
					if( $stmt->error ){
						// TODO エラー処理
						echo $stmt->error;
					}else{
						
					}
					
					$stmt->close();
				}
			}
			
			$mysqli->commit();
			
			$mysqli->close();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	// TODO メールはその内テンプレート読み込んで送信するよう修正、あと共通化
	private function sendPreRegisterMail(){
		try{
			$to			= $_REQUEST["mail_address"];
			$subject	= "LNS仮登録完了";
			$message	= "チーム名：" . $_REQUEST["team_name"] . "様\n\n" .
							"League of legends Nippon-no Salaryman 運営でございます。\n" .
							"チーム登録申請ありがとうございます。\n\n" .
							"今後の連絡にはDiscordを利用するため、Discordのインストールをお願いします。\n" .
							"Discordのインストールが完了しましたら、下記のURLをクリックして\n" .
							"LNSリーグのDiscordに参加をお願いします。\n" .
							"ディスコード会議室に参加後「【チーム名】参加しました」と発言下さい。\n\n" .
							"https://discord.gg/U3GU3Rv\n\n" .
							"ディスコードのルームに参加しましたら、必ずニックネームを下記の形に変更をお願いします。\n" .
							"チーム名@【個人名】\n\n" .
							"チームメンバーにLNS運営用ディスコード会議室のURLはお伝え頂いて構いませんが、\n" .
							"必ずチーム名を頭に付けるようお願い致します。\n\n" .
							"━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
							"※このメールにご返信をいただきましても、送信専用のアドレスのため、\n" .
							"　ご対応致しかねますのでご了承ください。\n" .
							"※本メールに関する一切の内容の無断転載および再配布を禁じます。\n" .
							"※本メールに心当たりのない場合はお手数をお掛けいたしますが\n" .
							"　破棄していただけますようお願いいたします。\n\n".
							"■LNS　公式サイト\n" .
							"　http://lns-lol.com/\n\n" .
							"━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
			
			$headers = 'From: no-reply@lns-lol.com' . "\r\n" .
				'Bcc: lns.official1@gmail.com,kyon.mg.lol@gmail.com,kurokkingu@gmail.com' . "\r\n" .
				'Reply-To: no-reply@lns-lol.com' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
			
			mail($to, $subject, $message, $headers);
			
		}catch( Exception $e ){
			return false;
		}
		return true;
	}
	
	// 正常画面表示
	// TODO 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
	public function displayNormal(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('TeamRegister_cmpl.tmpl');
	}
	
	// 確認画面表示
	// TODO その内共通化
	public function displayConfirm(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign("mail_address", $_REQUEST["mail_address"]);
		$smarty->assign("team_name", $_REQUEST["team_name"]);
		$smarty->assign("team_name_kana", $_REQUEST["team_name_kana"]);
		$smarty->assign("team_tag", $_REQUEST["team_tag"]);
		$smarty->assign("team_tag_kana", $_REQUEST["team_tag_kana"]);
		$smarty->assign("comment", $_REQUEST["comment"]);
		$smarty->assign("member", $_REQUEST["member"]);
		
		$smarty->display('TeamRegister_confirm.tmpl');
	}
	
	// 正常系とエラー系とで画面表示はBaseControllerあたりに共通化
	public function displayError(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('TeamRegister_err.tmpl');
	}
	
	public function detail(){
		$iTeamId = $_REQUEST["team_id"];
		
		$oDb = new Db();
		
		$oTeam = new Team( $oDb, $iTeamId );
		
		$aoTeamMember = $oTeam->getTeamMember( $oDb );
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "team_name"		, $oTeam->team_name );
		$smarty->assign( "team_name_kana"	, $oTeam->team_name_kana );
		$smarty->assign( "team_tag"			, $oTeam->team_tag );
		$smarty->assign( "team_tag_kana"	, $oTeam->team_tag_kana );
		$smarty->assign( "team_member"		, $aoTeamMember );
		
		$smarty->display('Team/TeamDetail.tmpl');
	}
/*
	public function showTeamList(){
        $sSelectTeamInfoSql = "SELECT " .
            "* " .
            "FROM " .
            "m_team " .
            "ORDER BY " .
            "team_id";

        $oDb = new Db();

        $oSelectTeamResult = $oDb->execute($sSelectTeamInfoSql);

        while ($row = $oSelectTeamResult->fetch_assoc()) {
            $sSelectMemberInfoSql = "SELECT " . "summoner_name " . "FROM " . "m_member " . "WHERE " . "team_id = " . $row["team_id"] . " " . "ORDER BY " . "member_id";

            $hsTeam = array(
                "team_name"      => $row["team_name"],
                "team_name_kana" => $row["team_name_kana"],
                "team_tag"       => $row["team_tag"],
                "team_tag_kana"  => $row["team_tag_kana"],
                "mail_address"   => $row["mail_address"],
                "comment"        => $row["comment"]
            );

            $oSelectMemberInfoResult = $oDb->execute($sSelectMemberInfoSql);

            $arrayMember = array();
            while ($row = $oSelectMemberInfoResult->fetch_assoc()) {
                $arrayMember[] = $row;
            }
            $hsTeam["member"] = $arrayMember;

            $ahsTeam[] = $hsTeam;
        }

        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->assign("team_list", $ahsTeam);
        $smarty->display('TeamList.tmpl');
	}

	private function displayTeamList(){
	}
	*/
}
