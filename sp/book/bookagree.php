<?php
/**
 * カード決済用約款確認画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );
require_once("include/2018/config.php");
#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/common.inc.php';
#require 'include/pokebook.inc.php';

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
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<title>クレジットカード支払い｜ぽけかる倶楽部</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="/sp/common2/css/booking.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("tags/head_tag.php"); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/sp/common/js/google-analytics.js"></script>
<script src="/sp/common/js/share.js"></script>
<script src="/sp/common/js/script.js"></script>

<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->


</head>
<body>
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->



<!-- ##### RAKUTEN ##### -->
<?php
    if($_SESSION['currentasid'] == ASID_RAKUTEN) {
	echo "<div style=\"border:2px solid orange; padding:5px;\"><a href=".LINK_RAKUTEN."><img src=\"/common/img/raketenlogo.gif\" Align=\"left\" Hspace=\"10\" Vspace=\"0\"></a>こちらのページからは、ぽけかる倶楽部のサイトとなります。<br/>ご予約は「ぽけかる倶楽部」にて行われます。楽天スーパーポイントの対象外となりますので、あらかじめご了承ください。<br />&nbsp;<Input type=\"button\" value=\"楽天トラベルへ戻る\" onClick=\"location.href='".LINK_RAKUTEN."'\"><br /></div>";
    }
?>
<!-- ##### /RAKUTEN ##### -->

<div class="main">
<!-- ##### BODY ##### -->
	<section>
		<h2 class="headline01 fs">
			イベント・ツアー予約(カード手続)
		</h2>

		<ol class="cart_stepbar">
			<li><span>1</span></li>
			<li><span>2</span></li>
			<li class="visited"><span>3</span></li>
			<li><span>4</span></li>
			<li><span>5</span></li>
			<li><span>6</span></li>			
		</ol>

		<div class="carttableTop">

		</div>

		<div class="mb10"></div>

		<h3 class="headline06">
			クレジットカード決済における諸注意
		</h3>
		<div class="carttableInfo">
			クレジットカードの決済手続きを進めてまいります。カード番号を次の画面で入力ください。お申込み後、ご予約をキャンセルされる場合は、カード会社を通じての返金となります。※お申込日から60日を過ぎている場合は、お客様の指定口座に返金いたします。※キャンセル料が発生している場合は、キャンセル料を差し引いた金額を指定口座に返金いたします。
		</div>
		
		<form name="FORM_BOTTUN" method="post" action="<?=$url_currentdir.$filename_current?>" target="_self">
			<input type="hidden" name="screenid" value="<?=$screenid_current?>" />
			<p class="wrapper" onclick="return judge()">
				<a id="p1" class="btn-detail" onClick="document.FORM_BOTTUN.submit();return false;">カード決済画面へ</a>
			</p>
		</form>
		<div class="mb5"></div>
		<div style="color:#F00; text-align: center; font-size:0.28rem;">
			※ボタンは2回以上押さないようお願いいたします。
		</div>
		
		<div class="mb20"></div>
		
		<h3 class="headline06">
			支払い方法再選択
		</h3>
		
		<div class="carttableInfo">
			クレジットカードを利用されない場合、下記ボタンより支払方法の再選択が可能です。
		</div>

		<p class="wrapper">
			<a href="<?php print($url_currentdir.FILENAME_BOOKINPUT_CARD); ?>" class="btn-style2">支払方法再選択画面へ</a>
		</p>

	</section>

<!-- ##### /BODY ##### --> 

</div>
<!-- / #page -->

<?php require_once("2018/footer.html"); ?>
        <script>
            var colorTag = 0;
            var set=0;
            //    var colors = ["#d3d3d3", "blue"];
            var colors = ["#d3d3d3"];
            function judge() {
                if (set==0){
                    set = 1;
                }else {
                    alert("只今処理中です。\nそのままお待ちください。");
                }
                document.getElementById("p1").style.backgroundColor = colors[colorTag];
            }
        </script>
