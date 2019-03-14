<?php
/**
 * お問い合わせ確認画面
 *
 *
 */

require_once("include/2018/config.php");
#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/common.inc.php';
#require 'include/pokebook.inc.php';

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
$screenid_before = SCREEN_INQURY;
$filename_before = FILENAME_INQURY;

// 自画面
$screenid_current = SCREEN_INQURYCONFIRM;
$filename_current = FILENAME_INQURYCONFIRM;

// 次画面
$screenid_next = SCREEN_INQURYEND;
$filename_next = FILENAME_INQURYEND;

$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');


// 遷移元画面の判定
if ($_POST['screenid'] == $screenid_current) {
    // 遷移元が自画面の場合

    // session画面IDに自画面IDをセット
    $_SESSION['screenid'] = $screenid_current;

    // 各処理
    // メールテンプレート読み込み
    $fname = MAIL_TEMPLATE_DIR.MAIL_TEMPLATE_INQUIRY;
    $mailTemplate = file_get_contents($fname);

    if ($mailTemplate == FALSE) {
        // ログ出力メッセージ
        $error_msg = "ファイル読み込みエラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($fname));
        // システムエラー画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
	exit();
    }

    // 問い合わせ送信
    $keydata['toiawasename'] = $_SESSION['toiawasename'];
    $keydata['toiawasemail'] = $_SESSION['toiawasemail'];
    $keydata['toiawasetext'] = $_SESSION['toiawasetext'];
    
    $ret = sendInquiryMail($keydata, $mailTemplate);
    if ($ret != SEND_INQUIRYMAIL_COMPLETE) {
        // ログ出力メッセージ
        $error_msg = "お問い合わせメール送信エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['toiawasemail']));
        // システムエラー画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
	exit();
    }

    // 次画面にリダイレクト
    $locate = "Location: " .$url_currentdir.$filename_next;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
} else {
  // 表示のみの制御なので、ここでは何もしない
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<title>お問い合せ｜ぽけかる倶楽部</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="/sp/common/css/page.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("/_data/tags/head_tag.php"); ?>
<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->

<style>
.bookboxM h2{
	height:40px !important;
}
.bookboxM h3{
	height:32px !important;
}
.canceltable textarea{
	width:100%;
	box-sizing:border-box;
	
	-webkit-appearance: none !important;
-moz-appearance: none !important;
appearance: none !important;
padding: 8px !important;
border: 1px solid #f1c7b2;
background: #ffe2d4;
font-size: 0.32rem;
}
</style>
</head>
  <body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

      <div class="main">
        <ul class="breadcrumb">
          <li><a href="../">日帰り旅行・ツアーTOP</a></li>
          <li>お問い合せフォーム(内容確認)</li>
        </ul>

<h1 class="headline01">お問い合せフォーム</h1>


<div class="mb30"></div>	
        <section>
<ol class="cancel_stepbar">
<li class="visited"><span>1</span><br>情報入力</li>
<li class="visited"><span>2</span><br>内容確認</li>
<li><span>3</span><br>送信完了</li>
</ol>


<div class="mb20"></div>

<div class="mlr15">			
		<p class="rem03">入力内容をご確認の上、内容に間違いがなければ、「この内容で送信する」ボタンを押してください。</p>
</div>

<form method="post" name="<?php print($screenid_current); ?>" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />

<div class="mb30"></div>		
                
				<div class="panel mlr15">
                <div class="panel-body canceltable">

<table>
                    	<tr>
                            <th>お名前<div class="c_dd0000">[必須]</div></th>
<td><?php print($_SESSION['toiawasename']); ?></td>
</tr>
<tr>
<th>メールアドレス<div class="c_dd0000">[必須]</div></th>
<td><?php print($_SESSION['toiawasemail']); ?></td>
</tr>
<tr>
<th>ご質問・ご意見・ご要望</th>
<td><?php print(nl2br(htmlspecialchars($_SESSION['toiawasetext']))); ?></td>
</tr>
</table>
</div>
                </div>



<div class="mb30"></div>
				
				<div class="mlr15">
                <a href="<?php echo $url_currentdir.FILENAME_INQURY; ?>" class="btn-detail">前の画面に戻る</a>
                
                <div class="mb10"></div>
                
                <a href="#" class="btn-detail" onclick="document.<?php print($screenid_current); ?>.submit();">この内容で送信する</a>
				</div>

</form>

<div class="mb100"></div>

</section>

</div>
</div>

<?php require_once("2018/footer.html"); ?>