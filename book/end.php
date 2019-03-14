<?php
/**
 * 予約確認画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );
require_once("include/2018/config.php");	#リニューアル時追加
#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/common.inc.php';
#require 'include/pokebook.inc.php';
require 'include/template_book.inc.php';  //add sjm.tk


ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
$screenid_before = SCREEN_ID_PREVIEW;
$filename_before = FILENAME_PREVIEW;

// 前画面_オーソリ終了画面
$screenid_before_card = SCREEN_ID_CARDRESULT;
$filename_before_card = FILENAME_CARDRESULT;

// 前画面_オーソリ終了(3D認証経由)画面
$screenid_before_card_3d = SCREEN_ID_CARDRESULT_3D;
$filename_before_card_3d = FILENAME_CARDRESULT_3D;

// 自画面
$screenid_current = SCREEN_ID_END;
$filename_current = FILENAME_END;

$system_error = array();

/*
 * Cookie削除
 */
if(isset($_COOKIE["pokecurrent"]) and !cmIsEmpty($_COOKIE["pokecurrent"])){
    // cookieが存在する場合、削除
    setcookie('pokecurrent', '', time() - 3600);
    
}

// DB接続
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

	//セッションデータ破棄
	$_SESSION=array();
	session_destroy();

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

// 前画面チェックOK遷移判定
if ($_SESSION['screenid'] == $screenid_before) {
    // 前画面チェックOK遷移
} elseif($_SESSION['screenid'] == $screenid_before_card || $_SESSION['screenid'] == $screenid_before_card_3d){ 
    // オーソリ結果の処理
    authokafter();
} else {
    // 前画面チェックOK遷移以外
    // 不正操作画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// session画面IDに自画面IDをセット
$_SESSION['screenid'] = $screenid_current;

//特定のASID
$isJCOM = false;
if($_SESSION['currentasid'] == ASID_JCOM) {
  $isJCOM = true;
}

//在庫識別子の取得
$sql = "SELECT thirdid FROM ".APPDB.".stocks ";
$sql .= " WHERE id = ?";

// プリペア
$stmt = $mdbapp->prepare($sql, array('text'));
if (PEAR::isError($stmt)) {
        // ログ出力メッセージ
        $error_msg = "在庫識別子検索SQLプリペアエラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
        return BOOK_FAILURE;
}
// SQL実行
$result = $stmt->execute(array($_SESSION['stock_id']));
if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "在庫識別子検索SQL実行エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid']), __FILE__, __LINE__);
        return BOOK_FAILURE;
}
// フェッチ
$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
$_SESSION['thirdid'] = $row['thirdid'];

//オーソリOK後処理実行
function authokafter() {
	global $mdbwww;
	global $mdbapp;

    // トランザクション開始
    $result = $mdbwww->beginTransaction();
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "トランザクション開始エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
    }

	//WWWオーソリ後ステータス更新
	//ステータスの更新_オーソリ実施(WWW)
	$wrkdate = date("Y/m/d H:i:s");
	// SQL文
	$sql = 'UPDATE d_booking SET ';
	$sql .= ' fromauthsystem = "'.FROM_AUTH_COMPLETE.'"';
	$sql .= ' ,fromauthsystemdate = "' .$wrkdate.'"';
	$sql .= ' ,fromsalesforce = "'.FROM_SALESFORCE_COMPLETE.'"';
	$sql .= ' ,fromsalesforcedate = "' .$wrkdate.'"';
	$sql .= ' ,tocustmermail = "'.TO_CUSTOMER_MAIL_NOT_COMPLETE_CARD.'"';
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
	}

	//オーソリ結果判定
	if ($_SESSION['auth_result'] == AUTH_OK) {        //オーソリ結果OK
		//カード区分作成
		$_SESSION['auth_card_info'] = substr($_SESSION['auth_inf_cd'],4,1);

		// トランザクション開始
		$result = $mdbapp->beginTransaction();
		if (PEAR::isError($result)) {
			// ログ出力メッセージ
			$error_msg = "トランザクション開始エラー";
			// ログ出力
			logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
		}

		//ステータスとオーソリ番号の更新_オーソリ結果OK(APP)
		// SQL文
		$sql = 'UPDATE bookings SET ';
		$sql .= ' authstatus = "'.$_SESSION['auth_result'].'"';
		$sql .= ' ,auth_uke_no = "' .$_SESSION['auth_uke_no'].'"';
		$sql .= ' ,cardinfo = "' .$_SESSION['auth_card_info'].'"';
                $sql .= ' ,card_company_code = "' .$_SESSION['auth_card_company_id'].'"';
		$sql .= ' ,auth_amount = "' .$_SESSION['auth_kingaku'].'"';
		$sql .= ' WHERE id = "'.$_SESSION['bookingnumber'].'"';
		// SQL実行(ステータス更新)
		$result = $mdbapp->query($sql);
		if (PEAR::isError($result)) {
			// ログ出力メッセージ
			$error_msg = "ステータス更新SQL実行エラー";
			// ログ出力
			logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
			// ロールバック
			$ret = $mdbapp->rollback();
			if (PEAR::isError($ret)) {
				// ログ出力メッセージ
				$error_msg = "ロールバックエラー";
				// ログ出力
				logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
			}
		}

		// トランザクションコミット
		$result = $mdbapp->commit();
		if (PEAR::isError($result)) {
			// ログ出力メッセージ
			$error_msg = "コミットエラー";
			// ログ出力
			logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
			// ロールバック
			$ret = $mdbapp->rollback();
			if (PEAR::isError($ret)) {
				// ログ出力メッセージ
				$error_msg = "ロールバックエラー";
				// ログ出力
				logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
			}
		}
	} else {
		// ログ出力メッセージ
		$error_msg = "オーソリ結果がNULL";
		// ログ出力
		logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

		// システムエラー画面にリダイレクト
		$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
		header("HTTP/1.0 301 Moved Permanently");
		header($locate);
		exit();
	}
}


// 催行日フォーマット変更
$ss_day = $_SESSION['saikouday'];
$temp_day = mktime(0, 0, 0, substr($ss_day, 4, 2), substr($ss_day, 6, 2), substr($ss_day, 0, 4));
$cond_day = date('Y-m-d', $temp_day);

// 予約申込日時
$bookingdt = $_SESSION['bookingdt'];
$bookingdt_y = date('Y', $bookingdt);
$bookingdt_m = date('m', $bookingdt);
$bookingdt_d = date('d', $bookingdt);
$bookingdt_h = date('H', $bookingdt);
$bookingdt_min = date('i', $bookingdt);

// イベント名,キャンセル規定区分はSESSIONパラメータから取得
//     画面出力の際はサニタイズを忘れないこと。
$eventname      = $_SESSION['eventname'];
$cancelkiteikbn = $_SESSION['cancelkiteikbn'];

// 子供料金未設定フラグ
$childfee_unsetflg = FALSE;
if (is_null($_SESSION['childfee'])) {
    // 子供料金がNULLの場合
    $childfee_unsetflg = TRUE;
} elseif ($_SESSION['childfee'] == 0) {
    // 子供料金が0の場合
    $childfee_unsetflg = TRUE;
}

// オプション未設定フラグ
$option_unsetflg = FALSE;
if (is_null($_SESSION['optname1'])) {
    // オプション１がNULLの場合
    $option_unsetflg = TRUE;
}

// キャンセル規定
$cancelrule = getcancelkitei($cancelkiteikbn,$_SESSION['eventid']);

#150622追加 グルーポン提携におけるカテゴリ抽出処理
$sql = "select cat.name_english from ".APPDB.".events evt,".APPDB.".sales_categories cat where evt.sales_category_id = cat.id and evt.eventcode = '{$_SESSION['eventid']}' ";
$res =& $mdbwww->query($sql);
$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
$event_category = urlencode($row['name_english']);

$mdbapp->disconnect();
$mdbwww->disconnect();

?>

<?php
    // 注文金額の算出
    $totalfee =  $_SESSION['adultfee'] * ($_SESSION['mannum'] + $_SESSION['womannum']);
    $totalfee += $_SESSION['childfee'] * ($_SESSION['boynum'] + $_SESSION['girlnum']);
    
    /*
     * リターゲティングタグ
     */
    // GoogleTag
$rttag_google =<<< DOC
<!-- Google Code for &#9632;&#9632;&#30003;&#12375;&#36796;&#12415;&#12501;&#12457;&#12540;&#12512;&#9632;&#9632;&#12288;&#20104;&#32004;&#23436;&#20102; Remarketing List -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1004406842;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "yNC_CN7v7AIQupD43gM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1004406842/?value=0&amp;label=yNC_CN7v7AIQupD43gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- Google Adwords Code 2012.11.09.add{ -->
<!-- Google Code for Adword-CV&#12288;2012.11 Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1038986319;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "7ExYCLGskgQQz9i27wM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1038986319/?value=0&amp;label=7ExYCLGskgQQz9i27wM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- }Google Adwords Code 2012.11.09.add -->

DOC;
    // BLADETag
$rttag_blade =<<< DOC
<script type="text/javascript">
<!--
var blade_co_account_id='2019';
var blade_group_id='convtrack10595';
-->
</script>
<script src="https://d-track.send.microad.jp/js/bl_track.js">
</script>
<script type="text/javascript">
<!--
var blade_co_account_id='2019';
var blade_group_id='';
-->
</script>
<script src="https://d-track.send.microad.jp/js/bl_track.js">
</script>

DOC;

//予約完了コンバージョンタグ2012.10.11
$tag_conv20121011 =<<< DOC
<!-- Yahoo Code for &#20104;&#32004;&#23436;&#20102;&#12304;PC&#12305; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var yahoo_conversion_id = 1000041695;
var yahoo_conversion_label = "MRycCIK9uwMQ7pew3AM";
var yahoo_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://s.yimg.jp/images/listing/tool/cv/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://b91.yahoo.co.jp/pagead/conversion/1000041695/?value=0&amp;label=MRycCIK9uwMQ7pew3AM&amp;guid=ON&amp;script=0&amp;disvt=true"/>
</div>
</noscript>

<!-- Google Code for &#20104;&#32004;&#23436;&#20102;&#12304;https&#12305; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1004406842;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "SIJ-CM7x7AIQupD43gM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1004406842/?value=0&amp;label=SIJ-CM7x7AIQupD43gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

DOC;

$tag_ydn_20130507 =<<< DOC

<img src="//b90.yahoo.co.jp/c?account_id=XhscYfkOLDV4km.WLeKk&transaction_id=&amount=&guid=ON" width="1" height="1" border="0" />

<!-- for pixel tracking -->
<img src="https://m.one.impact-ad.jp/pix?p=19576&p=19577&t=i"/>
DOC;

$tag_taggy =<<< DOC

<script type="text/javascript" src="https://a01.taggyad.jp/js/cv.js"></script>
<script type="text/javascript" src="https://a01.taggyad.jp/js/ext/poke_cv.js"></script>
DOC;

$tag_cv20131021 =<<< DOC

<!-- Google Code for &#12362;&#21839;&#21512;&#12379; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 979400865;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "5WHpCLfWyAcQofGB0wM";
var google_conversion_value = 0;
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/979400865/?value=0&amp;label=5WHpCLfWyAcQofGB0wM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

<!-- Yahoo Code for &#12362;&#21839;&#21512;&#12379; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var yahoo_conversion_id = 1000078573;
var yahoo_conversion_label = "93UhCJq_9AcQxrOZ2AM";
var yahoo_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://s.yimg.jp/images/listing/tool/cv/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://b91.yahoo.co.jp/pagead/conversion/1000078573/?value=0&amp;label=93UhCJq_9AcQxrOZ2AM&amp;guid=ON&amp;script=0&amp;disvt=true"/>
</div>
</noscript>

DOC;

$tag_cv20160229 =<<< DOC

<!-- Google Code for PC&#12467;&#12531;&#12496;&#12540;&#12472;&#12519;&#12531;&#12479;&#12464; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 927043468;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "d0gECLjxkmQQjJ-GugM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/927043468/?label=d0gECLjxkmQQjJ-GugM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

<!-- Yahoo Code for your Conversion Page -->
<script type="text/javascript">
    /* <![CDATA[ */
    var yahoo_conversion_id = 1000282296;
    var yahoo_conversion_label = "pjuICKnxkmQQ4-7uuAM";
    var yahoo_conversion_value = 0;
    /* ]]> */
</script>
<script type="text/javascript" src="//s.yimg.jp/images/listing/tool/cv/conversion.js">
</script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt="" src="//b91.yahoo.co.jp/pagead/conversion/1000282296/?value=0&label=pjuICKnxkmQQ4-7uuAM&guid=ON&script=0&disvt=true"/>
    </div>
</noscript>

DOC;

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<meta name="Description" content="" />
<meta name="Keywords" content="" />
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


<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '651670761687631'); // Insert your pixel ID here.
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=651670761687631&ev=PageView&noscript=1"
/></noscript>
<!-- DO NOT MODIFY -->
<!-- End Facebook Pixel Code -->

<style>
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
.book_wrap{
	width:900px;
	margin:0 auto;
}
.msgboard {
	border-radius: 5px;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border:4px solid #FB8B64;
	
    margin-bottom: 10px;
    padding: 7px 10px;
}
.inputField table tbody th {
	text-align:left;
	vertical-align:middle !important;
}
</style>
    <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
<script>
fbq('track', 'CompleteRegistration', {
value: 25.00,
currency: 'USD'
});
</script>
<body>
<?php include_once("/_data/tags/body_tag.php"); ?>
<!-- Yahoo Keyword Code 2012.11.09.add{ -->
<!-- Yahoo Code for &#12452;&#12505;&#12531;&#12488;&#12539;&#12484;&#12450;&#12540;&#30003;&#36796;&#23436;&#20102; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var yahoo_conversion_id = 1000017889;
var yahoo_conversion_label = "xwuRCJuqnAMQ9eK5zAM";
var yahoo_conversion_value = 5000;
/* ]]> */
</script>
<script type="text/javascript" src="https://s.yimg.jp/images/listing/tool/cv/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://b91.yahoo.co.jp/pagead/conversion/1000017889/?value=5000&amp;label=xwuRCJuqnAMQ9eK5zAM&amp;guid=ON&amp;script=0&amp;disvt=true"/>
</div>
</noscript>
<!-- }Yahoo Keyword Code 2012.11.09.add -->
<?
// tag埋め込み
printThunksTag('pc', $totalfee, $_SESSION['webbookingnumber'], $childfee_unsetflg)
?>

  <!-- ##### HEADER ##### -->
  <?php include_once("include/2018/header.html"); ?>

<div class="main">
        <div class="wrapper">

  <!-- ##### /HEADER ##### -->
          <!-- ##### /RAKUTEN ##### -->
        <?php
                if($_SESSION['currentasid'] == ASID_RAKUTEN) {
                        echo "<div style=\"border:2px solid orange; padding:5px;\"><a href=".LINK_RAKUTEN."><img src=\"/common/img/raketenlogo.gif\" Align=\"left\" Hspace=\"10\" Vspace=\"0\"></a>こちらのページからは、ポケカルのサイトとなります。&nbsp;<Input type=\"button\" value=\"楽天トラベルへ戻る\" style=\"WIDTH: 200px; HEIGHT: 20px; color: red; position: absolute; top: 120px; left: 600px;\" onClick=\"location.href='".LINK_RAKUTEN."'\"><br/>ご予約は「ポケカル」にて行われます。楽天スーパーポイントの対象外となりますので、あらかじめご了承ください。<br /><br /></div>";
                }
        ?>
  <!-- ##### BODY ##### -->
  <div id="bodyField">
    <div>
      <div id="mainField">

		<?php 
		if($isJCOM) { 
		?>
			<style type="text/css">
			h4#ttilEventTourReserve{
				background: url("/images/book/ttl_book_lots.jpg") no-repeat scroll left top rgba(0, 0, 0, 0);
			}
			</style>
		<?php 
		} 
		?>
		  
		  
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
				<li><span>3</span><br>カード手続</li>
				<li><span>4</span><br>カード番号</li>
				<li><span>5</span><br>カード情報確認</li>
				<li class="visited"><span>6</span><br><div class="newPoint">予約完了</div></li>
			</ol>
			
			<div class="mb50"></div>
			
			<p>
				<?php 
				if($isJCOM) { 
				?>
					<p class="p14"><span>以下の内容で予約受付ました。<br />
					予約状況を確認の上、入力頂いたメールアドレスに予約確定メールをお送りします。</span></p>
					<p class="p14"><span class="stonrg-red">※予約確定メールは、当落通知メールではございません。<br />
					予約受付期間終了後、抽選を行い、改めて当落通知メールをお送りします。</span></p>
				<?php 
				}else{ 

					if($cancelkiteikbn == 3){
				?>
					<div style="color:#000;font-weight:bold;font-size:36px;text-align: center;">お申込みいただき、ありがとうございました。</div>
					<div class="mb10"></div>
					<div style="font-size:14px;">後日、ご登録いただいたメールアドレスに<span style="color:#F00;">「予約確認メール」</span>をお送りします。メールが届くまでお待ちください。</div>

				<?php 
					}else{ 
				?>
						<div style="color:#000;font-weight:bold;font-size:36px;text-align: center;">お申込みいただき、ありがとうございました。</div>
						<div class="mb10"></div>
						<div style="font-size:14px;">ご登録いただいたメールアドレスに<span style="color:#F00;">「予約確認メール」</span>をお送りしました。<br>
						確認メールが届かない場合、お手数ですが、「ポケカルお客様センター（03-5652-7072）」までご連絡ください。</div>
				<?php 
					} 
				?>

				<?php 
				} 
				?>				

			</p>
			
			<div class="mb50"></div>
		  
		  	<form>

				<h2 class="headline02"><p>
					予約番号
				</p></h2>

				<div class="mb10"></div>  

				<div class="panel">
					<div class="carttable">

						<table>
							<tr>
								<th>
									予約番号
								</th>
								<td>
									<span style="font-size:28px;color:#000;font-weight:bold;">
										<?php print(htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES));?>
									</span>　
									<div style="font-size:14px;color:#F00;">
										※予約番号は、お客様のご予約内容を特定する際に必要となります。必ずお控えいただくようお願いいたします。
									</div>
								</td>
							</tr>
							<tr class="last">
								<th>
									予約日時
								</th>
								<td>
									<?php print($bookingdt_y."年".$bookingdt_m."月".$bookingdt_d."日　".$bookingdt_h."：".$bookingdt_min);?>
								</td>
							</tr>

						</table>
					</div>
				</div>
				
				<h2 class="headline02"><p>
					<?php 
					if($isJCOM) { 
					?>
						ご予約(抽選)イベント・ツアー
					<?php 
					}else{ 
					?>
						ご予約イベント
					<?php 
					} 
					?>
				</p></h2>
				
				<div class="mb10"></div>  

				<div class="panel">
					<div class="carttable">

						<table>
							<tr>
								<th>
									<?php 
									if($isJCOM) { 
									?>
										イベント・ツアー名
									<?php 
									}else{ 
									?>
										イベント名
								  	<?php 
									} 
									?>
								</th>
								<td>
									<?php 
										print(htmlspecialchars($eventname, ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr>
								<th>
									日付
								</th>
								<td>
									<?php
										print(date('Y', $temp_day).'年');
										print(date('m', $temp_day).'月');
										print(date('d', $temp_day).'日(');
										setlocale(LC_TIME, 'ja_JP.UTF-8');
										print(strftime('%A', $temp_day).')');
									?>

									<?php
										if (!empty($_SESSION['thirdid'])) {
											print("&nbsp;".htmlspecialchars($_SESSION['thirdid'], ENT_QUOTES));
										}
									?>
								</td>
							</tr>
							<tr>
								<th>
									申し込み人数
								</th>
								<td>
									<div class="proposers">
										大人　
										<span class="stonrg-red">
											<?php
											//割引が適用されているか判断
											if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
												print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['baseadultfee'].")", ENT_QUOTES));
											} else {
												print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES));
											}
											?>
											円
										</span>
										<?php 
										print(htmlspecialchars($_SESSION['mannum'], ENT_QUOTES));
										?>
										人

										<?php
										// 子供料金未設定の判断
										if (!$childfee_unsetflg) {
										?>
											&nbsp;／&nbsp;
											子供　
											<span class="stonrg-red">
												<?php
												//割引が適用されているか判断
												if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
													print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['basechildfee'].")", ENT_QUOTES));
												} else {
													print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES));
												}
												?>

												円
											</span>
											<?php 
											print(htmlspecialchars($_SESSION['boynum'], ENT_QUOTES));
											?>
											人
											<?php
										}
										?>
										<?php
										//イベントカテゴリがチケットであれば送料発生
										if($_SESSION['postage'] != "" || $_SESSION['postage'] != null) {
										?>                                                                                
										<span class="x-small">
											<strong></br>
											※当イベントはチケット送料が
											<?php
											print($_SESSION['postage']);
											?>
											円発生します。あらかじめご了承ください。
											</strong>
										</span>                                                                                        
										<?php
										}
										?>
									</div>
								</td>
							</tr>	
							<tr class="last">
								<?php
								// オプションがあるイベントか判断
								if (!$option_unsetflg) {
									print("<th>選択肢　　<span class=\"price\"></span></th><td><div>");
									// オプションありのイベントの場合
									//オプション数の上限までループ
									for($optcnt = 1; $optcnt < OPTSUBJECT + 1; $optcnt++){
										$optname = 'optname'.$optcnt;
										$optnum = 'optnum'.$optcnt;
										$optnamePLUS = 'optnum'.($optcnt+1);
										?>                                                                    
										<?php print(htmlspecialchars($_SESSION[$optname], ENT_QUOTES));?>
										<?php print(htmlspecialchars($_SESSION[$optnum], ENT_QUOTES));
										if($_SESSION[$optname] != ""):
										?> 人
                                        <?php
										endif;
										if($optcnt < OPTSUBJECT && $_SESSION[$optnamePLUS] != ""):
										?>
										&nbsp;／&nbsp;
										<?php                                                                                        
										endif;
									}
								}
								?>
							</tr>

						</table>
					</div>
				</div>
		
				<h2 class="headline02"><p>
					申込者
				</p></h2>
				
				<div class="mb10"></div> 

				<div class="panel">
					<div class="carttable">
						<table>
							<tr>
								<th>
									氏名
								</th>
								<td>
									<?php 
										print(htmlspecialchars($_SESSION['lastnamekanji'], ENT_QUOTES)."　".htmlspecialchars($_SESSION['firstnamekanji'], ENT_QUOTES));
									?>							
								</td>
							</tr>
							<tr>
								<th>
									フリガナ
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['lastnamekana'], ENT_QUOTES)."　".htmlspecialchars($_SESSION['firstnamekana'], ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr>
								<th>
									性別
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['gender'], ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr class="last">
								<th>
									生年月日
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['birth_year'], ENT_QUOTES)."年".htmlspecialchars($_SESSION['birth_month'], ENT_QUOTES)."月".htmlspecialchars($_SESSION['birth_day'], ENT_QUOTES)."日");
									?>
								</td>
							</tr>
						</table>
					</div>
				</div>
		
				<h2 class="headline02"><p>
					ご連絡先
				</p></h2>

				<div class="mb10"></div>  

				<div class="panel">
					<div class="carttable">
						<table>
							<tr>
								<th>
									郵便番号
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['postalcode1'], ENT_QUOTES));
									?>
									-
									<?php 
									print(htmlspecialchars($_SESSION['postalcode2'], ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr>
								<th>
									都道府県
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['address1'], ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr>
								<th>
									市区
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['address2'], ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr>
								<th>
									町村 番地<br>アパート マンション
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['address3'], ENT_QUOTES));
									?>
								</td>
							</tr>
							<tr>
								<th>
									電話番号
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['telephone1'], ENT_QUOTES));
									?>
									-
									<?php
									print(htmlspecialchars($_SESSION['telephone2'], ENT_QUOTES));
									?>
									-
									<?php
									print(htmlspecialchars($_SESSION['telephone3'], ENT_QUOTES));
									?>

								</td>
							</tr>
							<tr class="last">
								<th>
									Eメールアドレス
								</th>
								<td>
									<?php 
									print(htmlspecialchars($_SESSION['mailaddress'], ENT_QUOTES));
									?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			
				<div style="margin-bottom:80px;"></div>
	  			
				<p class="btn-detail"><a href="/index.html">トップページへ</a></p>	
				
			</form>

		  
		</section>
		  
		<div class="mb100"></div>		  
		  

      </div>
    </div>
  </div>
  
  </div><!-- book_wrap -->
  <!-- ##### /BODY ##### --> 
  <!-- ##### TAG ##### -->
  <?php
    echo $rttag_google;
    echo $rttag_blade;
    echo $tag_conv20121011;
    echo $tag_ydn_20130507;
    echo $tag_taggy;
    echo $tag_cv20131021;
	echo $tag_cv20160229;
  ?>
  <!-- ##### /TAG ##### -->

</div>
<script type="text/javascript">
var _mvq = window._mvq || [];window._mvq = _mvq;
_mvq.push(['$setAccount', 'm-127182-0']);
_mvq.push(['$setGeneral', 'ordercreate', '','','']);
_mvq.push(['$logConversion']);
_mvq.push(['$addOrder',/*注文番号*/ '<?php echo htmlspecialchars($_SESSION['webbookingnumber'], ENT_QUOTES); ?>', /*注文金額*/ '<?php echo htmlspecialchars($totalfee, ENT_QUOTES); ?>']);
_mvq.push(['$addItem', /*注文番号*/ '', /*商品ID*/ '', /*商品名*/ '', /*価格*/ '', /*個数*/ '', /*商品ページURL*/ '', /*商品画像URL*/ '']);
_mvq.push(['$logData']);
</script>

<?php
#MMDBとの連携
if($_SESSION['mailmag'] == 'ok'){
	$email = rawurlencode(addslashes($_SESSION['mailaddress']));
	$gender = rawurlencode(addslashes($_SESSION['gender']));
	$pref = rawurlencode(addslashes($_SESSION['address1']));
	$url = "http://odekake.poke.co.jp/mmdb_api/api_add.php?email={$email}&gender={$gender}&pref={$pref}";
	file_get_contents($url);
}
?>

<!-- CAW Tracking Conversion Code Start -->
<script>
!function(e,t,n,r,a,c,s){e.TrackerObject=a,e[a]=e[a]||
function(){(e[a].q=e[a].q||[]).push(arguments)},
e[a].l=1*new Date,c=t.createElement(n), s=t.getElementsByTagName(n)[0],c.async=1,c.src=r+"?_t="+e[a].l,
s.parentNode.insertBefore(c,s)} (window,document,"script","https://script-ad.mobadme.jp/js/tracker.js","trk");
trk("configure", "type", "4");
trk("configure", "status", "1");
trk("configure", "uid", "<?php print(htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES));?>");
trk("configure", "tr_p1", "1");
trk("configure", "tr_p2", "1");
trk("configure", "tr_p3", "<?php print(htmlspecialchars($_SESSION['calcres'] , ENT_QUOTES)); ?>");
trk("configure", "tr_p4", "0");
trk("configure", "tr_p5", "0");
trk("configure", "tr_p6", "JPY");
trk("configure", "tr_p7", "1");
trk("configure", "tr_p8", "<?php if($_SESSION['paymethod'] == 'card') echo "1" ; else echo "3"; ?>");
trk("configure", "tr_p9", "<?php echo $_SESSION['eventid']; ?>");
trk("send", "41987");
</script>
<!-- CAW Tracking Conversion Code End -->

<?php include_once("include/2018/footer.html"); ?>
