<?php
/**
 * カード決済用約款確認画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );
require_once("include/2018/config.php");	#リニューアル時追加
#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/common.inc.php';
#require 'include/pokebook.inc.php';
require 'include/template.inc.php';
require 'include/template_book.inc.php';

header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s"). " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
$screenid_before = SCREEN_ID_PREVIEW;
$filename_before = FILENAME_PREVIEW;

// 自画面
$screenid_current = SCREEN_ID_BOOKAGREE;
$filename_current = FILENAME_BOOKAGREE;

// 次画面
$screenid_next = SCREEN_ID_CARDINPUT;
$filename_next = FILENAME_CARDINPUT;

$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

//特定のASID
$isJCOM = false;
if($_SESSION['currentasid'] == ASID_JCOM) {
  $isJCOM = true;
}

// DB接続
$mdbwww =& MDB2::connect(DB_DSN_WWW);
if(PEAR::isError($mdbwww)) {
    // ログ出力メッセージ
    $error_msg = "DB接続エラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

$mdbapp =& MDB2::connect(DB_DSN_APP);
if(PEAR::isError($mdbapp)) {
    // ログ出力メッセージ
    $error_msg = "DB接続エラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// 催行日フォーマット変更
$ss_day = $_SESSION['saikouday'];
$temp_day = mktime(0, 0, 0, substr($ss_day, 4, 2), substr($ss_day, 6, 2), substr($ss_day, 0, 4));
$cond_day = date('Y-m-d', $temp_day);

// ロックファイル名
$lockfilename = LOCKFILE_DIRECTORY.LOCKFILE_PREFIX.session_id();

// 遷移元画面の判定
$past_screen_ind = PAST_SCREEN_IND_OTHER;
if (array_key_exists('screenid', $_POST)) {
    // $_POST['screenid']が定義済み

    if ($_POST['screenid'] == $screenid_current) {
        // $_POST['screenid']が自画面
        $past_screen_ind = PAST_SCREEN_IND_SELF;
    } else {
        // $_POST['screenid']がその他
        $past_screen_ind = PAST_SCREEN_IND_OTHER;
    }
} else {
    // $_POST['screenid']が未定義
    // 前画面チェックOK遷移判定
    if ($_SESSION['screenid'] == $screenid_before) {
        // 前画面チェックOK遷移
        $past_screen_ind = PAST_SCREEN_IND_BEFORE;
    } else {
        // 前画面チェックOK遷移以外
        $past_screen_ind = PAST_SCREEN_IND_OTHER;
    }
}
switch ($past_screen_ind) {
    case PAST_SCREEN_IND_SELF:
        // 遷移元が自画面の場合

        // 経過時間チェック
        if (checkElapsedTime() == FALSE) {
                // タイムアウト画面にリダイレクト
                $locate = "Location: " .$url_currentdir.FILENAME_TIMEOUT;
                header("HTTP/1.0 301 Moved Permanently");
                header($locate);
                exit();
        }

        // session画面IDに自画面IDをセット
        $_SESSION['screenid'] = $screenid_current;

        $locate = "Location: " .$url_currentdir.$filename_next;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);

        break;

    case PAST_SCREEN_IND_BEFORE:
        // 遷移元が前画面の場合

        // 操作開始時刻保存
        setOperationStartTime();

        break;

    default:
        // 遷移元がその他の場合

        // 不正操作画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
        exit();

        break;

}

// キャンセル規定
$cancelrule = getcancelkitei($_SESSION['cancelkiteikbn'],$_SESSION['eventid']);

//オーソリ実行

// トランザクション開始
$result = $mdbwww->beginTransaction();
if (PEAR::isError($result)) {
	// ログ出力メッセージ
	$error_msg = "トランザクション開始エラー";
	// ログ出力
	logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
	// システムエラー画面にリダイレクト
	$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	header("HTTP/1.0 301 Moved Permanently");
	header($locate);
	exit();
}

//ステータスの更新_オーソリ実施(WWW)
$wrkdate = date("Y/m/d H:i:s");
// SQL文
$sql = 'UPDATE d_booking SET ';
$sql .= ' poke_app_bookingnumber = "'.$_SESSION['bookingnumber'].'"';
$sql .= ' ,toauthsystem = "'.TO_AUTH_COMPLETE.'"';
$sql .= ' ,toauthsystemdate = "' .$wrkdate.'"';
$sql .= ' WHERE bookingnumber = "'.$_SESSION['webbookingnumber'].'"';
// SQL実行(ステータス更新)
$result = $mdbwww->query($sql);
if (PEAR::isError($result)) {
	// ログ出力メッセージ
	$error_msg = "ステータス更新SQL実行エラー";
	// ログ出力
	logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
	// ロールバック
	$ret = $mdbwww->rollback();
	if (PEAR::isError($ret)) {
		// ログ出力メッセージ
		$error_msg = "ロールバックエラー";
		// ログ出力
		logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
	}
	// システムエラー画面にリダイレクト
	$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	header("HTTP/1.0 301 Moved Permanently");
	header($locate);
	exit();
}

// トランザクションコミット
$result = $mdbwww->commit();
if (PEAR::isError($result)) {
	// ログ出力メッセージ
	$error_msg = "コミットエラー";
	// ログ出力
	logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
	// ロールバック
	$ret = $mdbwww->rollback();
	if (PEAR::isError($ret)) {
		// ログ出力メッセージ
		$error_msg = "ロールバックエラー";
		// ログ出力
		logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
	}
	// システムエラー画面にリダイレクト
	$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	header("HTTP/1.0 301 Moved Permanently");
	header($locate);
	exit();
}

//オーソリ金額を算出
//$totalfee = $_SESSION['calcres'];
//オーソリデータ作成
//$form = tryauth($_SESSION['bookingnumber'],$totalfee);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="" />
<meta name="Keywords" content="" />
<meta name="copyright" content="" />
<title>イベントツアー予約 | ポケカル</title>
<link href="/css/book.css" rel="stylesheet" type="text/css" />

<link rel="stylesheet" href="/common2/css/font-awesome.min.css">
<link rel="stylesheet" href="/common2/css/jquery-ui.css">
<link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
<link rel="stylesheet" href="/common2/css/slick.css">
<link rel="stylesheet" href="/common2/css/style.css">

<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="cleartype" content="on">
<![endif]-->
    <?php include_once("/_data/tags/head_tag.php"); ?>
<style>
.submit_btn2 {
	-webkit-appearance:none;
	border:none;
    display: block;
    width: 410px;
    margin: 0 auto;
    margin-bottom: 15px;
    text-decoration: none;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    background: #FB8B64;
    color: #FFF;
	font-weight:bold;
    text-align: center;
    line-height: 40px;
    box-shadow: 0px 2px 0px #FE733B;
    -moz-box-shadow: 0px 2px 0px #FE733B;
    font-size: 18px;
}
.titlebar {
    width: 100%;
    background: #ECECEC;
    color: #404040;
    font-size: 20px;
    font-weight: bold;
    -moz-box-shadow: 0px 2px 0px #A6A6A6;
    -webkit-box-shadow: 0px 2px 0px #A6A6A6;
    -o-box-shadow: 0px 2px 0px #A6A6A6;
    -ms-box-shadow: 0px 2px 0px #A6A6A6;
    box-shadow: 0px 2px 0px #A6A6A6;
    padding: 10px 15px 7px 15px;
    box-sizing: border-box;
    margin: 0 0 20px 0px;
}
.inputField table tbody th {
	text-align:left;
	vertical-align:middle !important;
}

.btn-detail2 {
  text-align: center;
}
.btn-detail2 a {
  display: block;
  color: #333333;
  padding: 12px 8px 10px;
  background: #d9d9d9;
  font-size: 1.8rem;
  text-decoration:none;
}

</style>
</head>
<body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

      <div class="main">
        <div class="wrapper">

  <!-- ##### /HEADER ##### -->
  <!-- ##### BODY ##### -->
  <div id="bodyField">
    <div>
      <div id="mainField">

		<div class="mb10"></div>
			
		<h1 class="headline01"><p>
			<?php 
			if($isJCOM) {
			?>
				イベント・ツアー予約(抽選) 
			<?php 
			}else{
			?>
			 	イベント・ツアー予約
			<?php
			} 
			?>
		</p></h1>
		  
		<div class="mb50"></div>			
        <section id="mainField">

			<ol class="cart_stepbar">
				<li><span>1</span><br>予約内容入力</li>
				<li><span>2</span><br>予約内容確認</li>
				<li class="visited"><span>3</span><br><div class="newPoint">カード手続</div></li>
				<li><span>4</span><br>カード番号</li>
				<li><span>5</span><br>カード情報確認</li>
				<li><span>6</span><br>予約完了</li>
			</ol>
			
			<div class="mb20"></div>
		  
			<div class="mb50"></div>
		  
			<h2 class="headline02"><p>
				クレジットカード決済における諸注意
			</p></h2>

			<div class="mb10"></div>	
		  
			<div class="panel">
				<div class="carttable">	  
					<table>
						<tr class="last">
							<td class="single" colspan="2">
								クレジットカードの決済手続きを進めてまいります。カード番号を次の画面で入力ください。お申込み後、ご予約をキャンセルされる場合は、カード会社を通じての返金となります。※お申込日から60日を過ぎている場合は、お客様の指定口座に返金いたします。※キャンセル料が発生している場合は、キャンセル料を差し引いた金額を指定口座に返金いたします。
							</td>
						</tr>
					</table>								
				</div>
			</div>
		  
			<form name="frm" method="post" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
				<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
				
	  			<div style="margin-bottom:50px;"></div>
	  			
	  			<p class="btn-detail"><a href="javascript:void(0);" onclick="document.frm.submit();">カード決済画面へ</a></p>
				
				<div class="mb10"></div>

				<div style="color:#F00; text-align: center; font-weight: bold;">
					※ボタンは2回以上押さないようお願いいたします。
				</div>
			</form>
		  
		  	<div class="mb100"></div>
		  
			<h2 class="headline02"><p>
				支払い方法再選択
			</p></h2>
		  
		  	<div class="p16">
				クレジットカードを利用されない場合、下記ボタンより支払方法の再選択が可能です。
			</div>
			
		  	<div class="mb20"></div>
		  
			<p class="btn-detail2"><a href="<?php print($url_currentdir.FILENAME_BOOKINPUT_CARD); ?>/">支払い方法再選択画面へ</a></p>
            
			<div class="mb50"></div>		  
		
		</section>

      </div>
    </div>
  </div>
  </div>
  </div>
</div>
       <?php include_once("include/2018/footer.html"); ?>