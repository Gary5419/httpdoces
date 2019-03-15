<?php
/**
 * お問い合わせ入力画面
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
session_regenerate_id();

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 自画面
$screenid_current = SCREEN_INQURY;
$filename_current = FILENAME_INQURY;

// 次画面
$screenid_next = SCREEN_INQURYCONFIRM;
$filename_next = FILENAME_INQURYCONFIRM;

$user_error = array();
$system_error = array();

$toiawasename = '';
$toiawasemail = '';
$toiawasetext = '';

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

// 遷移元画面の判定
if (isset($_POST['screenid']) && $_POST['screenid'] == $screenid_current) {
    // 遷移元が自画面の場合

    // session画面IDに自画面IDをセット
    $_SESSION['screenid'] = $screenid_current;

    // 自画面からのデータの入力チェック
    checkPostData($user_error);

    // チェック判定
    if (cmIsEmpty($user_error)) {   // チェックOK
        // 入力データを格納
        storePostData();

	// データを格納
	if(isset($_POST['toiawasename'])){
	    $toiawasename = $_POST['toiawasename'];
	}
	if(isset($_POST['toiawasemail'])){
	    $toiawasemail = $_POST['toiawasemail'];
	}
	if(isset($_POST['toiawasetext'])){
	    $toiawasetext = $_POST['toiawasetext'];
	}

        // 次画面にリダイレクト
        $locate = "Location: " .$url_currentdir.$filename_next;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);

    } else {                        // チェックNG
        // 表示のみの制御なので、ここでは何もしない
    }
} else {
    // 遷移元が自画面以外の場合

    // 引継ぎデータ以外をクリア
    clearData();

    // データを格納
    if(isset($_POST['toiawasename'])){
	$_SESSION['toiawasename'] = $_POST['toiawasename'];
    }
    if(isset($_POST['toiawasemail'])){
	$_SESSION['toiawasemail'] = $_POST['toiawasemail'];
    }
    if(isset($_POST['toiawasetext'])){
	$_SESSION['toiawasetext'] = $_POST['toiawasetext'];
    }
}

/**
 * データ入力チェック
 */
function checkPostData(&$user_error) {
    // 氏名(漢字)/namekanji/必須/20
    isParamCheckOK($user_error, $_POST['toiawasename'], 'お名前(漢字)', true, 20, 1, '', '^[一-龠ぁ-んァ-ヶー]+[　]?[一-龠ぁ-んァ-ヶー]+$');
    // メールアドレス/mailaddress/必須/100
    isParamCheckOK($user_error, $_POST['toiawasemail'], 'メールアドレス', true, 100, 1, '', '^[0-9a-zA-Z][0-9a-zA-Z_.-]*@[0-9a-z][0-9a-z_.-]+\.[a-z]+$');
}

/**
 * データ格納
 */
function storePostData() {
    $_SESSION['toiawasename'] = $_POST['toiawasename'];
    $_SESSION['toiawasemail'] = $_POST['toiawasemail'];
    $_SESSION['toiawasetext'] = $_POST['toiawasetext'];
}

/**
 * データクリア
 */
function clearData() {
    $_SESSION['toiawasename'] = '';
    $_SESSION['toiawasemail'] = '';
    $_SESSION['toiawasetext'] = '';
}
define("H1TXT","お問い合わせ｜ポケカル");
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="description" content="ポケカルへのお問い合わせはこちらのフォームからお願いします。"/>
<meta name="keywords" content="お問い合わせ,メールフォーム,ポケカル" />
<title>お問い合わせ｜ポケカル</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="/sp/common/css/page.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("tags/head_tag.php"); ?>
<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->

<style>
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
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

      <div class="main">
        <ul class="breadcrumb">
          <li><a href="../">ポケカルTOP</a></li>
          <li>お問い合せフォーム</li>
        </ul>

<h1 class="headline01">お問い合せフォーム</h1>


<div class="mb30"></div>	
        <section>
<ol class="cancel_stepbar">
<li class="visited"><span>1</span><br>情報入力</li>
<li><span>2</span><br>内容確認</li>
<li><span>3</span><br>送信完了</li>
</ol>


<div class="mb20"></div>

<div class="mlr15">			
		<p class="rem03">ご質問・ご意見・ご要望を当お問い合わせフォームよりお送りください。お問い合わせを頂いた内容により、お返事を差し上げられない場合、または、お返事にお時間をいただく場合があります。予めご了承いただきますようお願いします。</p>


<?php
    // エラーメッセージ出力
    if (!cmIsEmpty($user_error)) {
	print("<div class=\"inputerror\">");
	foreach ($user_error as $var) {
	    print("※".$var."<br />");
	}
	print("</div>");
    }
?>
</div>

<form method="post" name="frm_toiawase" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />

<div class="mb30"></div>		
                
				<div class="panel mlr15">
                <div class="panel-body canceltable">

<table>
                    	<tr>
                            <th>お名前<div class="c_dd0000">[必須]</div></th>
<td><input type="text" name="toiawasename" value="<?php print($toiawasename); ?>" style="-webkit-appearance: none !important;border-radius:0;" /></td>
</tr>
<tr>
<th>メールアドレス<div class="c_dd0000">[必須]</div></th>
<td><input type="text" name="toiawasemail" value="<?php print($toiawasemail); ?>" style="-webkit-appearance: none !important;border-radius:0;" /></td>
</tr>
<tr>
<th>ご質問・ご意見・ご要望</th>
<td><textarea name="toiawasetext" rows="10" value="<?php print($toiawasetext); ?>" style="-webkit-appearance: none !important;border-radius:0;"></textarea></td>
</tr>
</table>
</div>
                </div>


               
                <div class="mb50"></div>
				
				<div class="mlr15">
                <p class="btn-detail"><a href="javascript:void(0);" class="btn-detail" onclick="document.cancelform.submit();">入力内容確認</a></p>
                
				</div>
				</form>
                
                <div class="mb100"></div>


</section>

</div>
</div>

<?php require_once("2018/footer.html"); ?>