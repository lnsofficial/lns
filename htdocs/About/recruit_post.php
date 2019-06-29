<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LNS</title>
    <!-- Bootstrap -->
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet" type="text/css">
    <link href="/css/about.css" rel="stylesheet" type="text/css" media="screen">
    

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
  <!--The following script tag downloads a font from the Adobe Edge Web Fonts server for use within the web page. We recommend that you do not modify it.--><script>var __adobewebfontsappname__="dreamweaver"</script><script src="http://use.edgefonts.net/open-sans:n3,n8,n4:default;overlock:n4:default;poiret-one:n4:default;paytone-one:n4:default.js" type="text/javascript"></script>
</head>
  <body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . "/include/header.html"); ?>
  <div class="container-fluid">

<?php
/*******************************
 データの受け取り 
*******************************/
$namae      = $_POST["namae"];      //お名前
$discordid  = $_POST["discordid"];  //メールアドレス
$naiyou     = $_POST["naiyou"];     //お問合せ内容

//危険な文字列を入力された場合にそのまま利用しない対策
$namae      = htmlspecialchars($namae, ENT_QUOTES);
$discordid  = htmlspecialchars($discordid, ENT_QUOTES);
$naiyou     = htmlspecialchars($naiyou, ENT_QUOTES);

/*******************************
 未入力チェック 
*******************************/
$errmsg = '';   //エラーメッセージを空にしておく
if ($namae == '') {
    $errmsg = $errmsg.'<p>お名前が入力されていません。</p>';
}
if ($discordid == '') {
    $errmsg = $errmsg.'<p>ディスコードIDが入力されていません。</p>';
}
if ($naiyou == '') {
    $errmsg = $errmsg.'<p>お問合せ内容が入力されていません。</p>';
}

/*******************************
 メール送信の実行
*******************************/
if ($errmsg != '') {
    //エラーメッセージが空ではない場合には、[前のページへ戻る]ボタンを表示する
    echo $errmsg;
    
    //[前のページへ戻る]ボタンを表示する
    echo '<form method="post" action="/About/recruit.html">';
    echo '<input type="hidden" name="namae" value="'.$namae.'">';
    echo '<input type="hidden" name="discordid" value="'.$discordid.'">';
    echo '<input type="hidden" name="naiyou" value="'.$naiyou.'">';
    echo '<input type="submit" name="backbtn" value="前のページへ戻る">';
    echo '</form>';
} else {
    //エラーメッセージが空の場合には、メール送信処理を実行する
    //メール本文の作成
    $honbun = "";
    $honbun .= "メールフォームよりお問い合わせがありました。\n\n";
    $honbun .= "【お名前】\n";
    $honbun .= $namae."\n\n";
    $honbun .= "【メールアドレス】\n";
    $honbun .= "lns.official2@gmail.com\n\n";
    $honbun .= "【お問い合わせ内容】\n";
    $honbun .= $naiyou."\n\n";

    //エンコード処理
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    //メールの作成
    $mail_to        = "lns.official1@gmail.com";                              //送信先メールアドレス
    $mail_subject   = "ID[".$discordid."]からメールフォームよりお問い合わせ"; //メールの件名
    $mail_body      = $honbun;                                                //メールの本文
    $mail_header    = "from:lns.official2@gmail.com";                         //送信元として表示されるメールアドレス
    
    //メール送信処理
    $mailsousin     = mb_send_mail($mail_to, $mail_subject, $mail_body, $mail_header);
    
    //メール送信結果
    if($mailsousin == true) {
        echo '<p>お問い合わせメールを送信しました。</p>';
    } else {
        echo '<p>メール送信でエラーが発生しました。</p>';
    }
}
?>

  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.html"); ?>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
    <script src="/js/jquery-1.11.3.min.js"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed --> 
    <script src="/js/bootstrap.js"></script>
  </body>
</html>