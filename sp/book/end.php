<?php
/**
 * 予約確認画面
 *
 *
 */
ini_set( 'display_errors', 'On' );
require_once("include/2018/config.php");

$cms->setAllSpecialHtml();
$data2 = $cms->res_data;

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

<!-- Google Adwords Code 2013.08.19.add{ -->
<!-- Google Code for &#12473;&#12510;&#12507;&#20104;&#32004;CV&#12304;2013.7&#65374;&#12305; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1004406842;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "exTACLacwAcQupD43gM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1004406842/?value=0&amp;label=exTACLacwAcQupD43gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- }Google Adwords Code 2013.08.19.add -->

DOC;
    // BLADETag
$rttag_blade =<<< DOC
<script type="text/javascript">
<!--
var blade_co_account_id='2019';
var blade_group_id='convtrack10958';
-->
</script>
<script src="https://d-track.send.microad.jp/js/bl_track.js">
</script>

DOC;

//予約完了コンバージョンタグ2012.10.11
$tag_conv20121011 =<<< DOC
<!-- Yahoo Code for &#20104;&#32004;&#23436;&#20102;&#12304;&#12473;&#12510;&#12507;&#12305; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var yahoo_conversion_id = 1000041695;
var yahoo_conversion_label = "nCEMCPq9uwMQ7pew3AM";
var yahoo_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://s.yimg.jp/images/listing/tool/cv/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://b91.yahoo.co.jp/pagead/conversion/1000041695/?value=0&amp;label=nCEMCPq9uwMQ7pew3AM&amp;guid=ON&amp;script=0&amp;disvt=true"/>
</div>
</noscript>


<!-- Google Code for &#20104;&#32004;&#23436;&#20102;&#12304;&#12473;&#12510;&#12507;&#12305; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1001285664;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "t8TVCKCpxgMQoNC53QM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1001285664/?value=0&amp;label=t8TVCKCpxgMQoNC53QM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

DOC;

$tag_ydn_20130507 =<<< DOC

<img src="https://b90.yahoo.co.jp/c?account_id=XhscYfkOLDV4km.WLeKk&transaction_id=&amount=" width="1" height="1" border="0" />
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

<!-- Google Code for SP&#12467;&#12531;&#12496;&#12540;&#12472;&#12519;&#12531;&#12479;&#12464; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 927043468;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "-JXSCOKGmmQQjJ-GugM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/927043468/?label=-JXSCOKGmmQQjJ-GugM&amp;guid=ON&amp;script=0"/>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<title>イベントツアー予約｜ポケカル</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="/sp/common2/css/booking.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("/_data/tags/head_tag.php"); ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/sp/common/js/google-analytics.js"></script>
<script src="/sp/common/js/share.js"></script>
<script src="/sp/common/js/script.js"></script>

<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->

<style>
	
hr{
   border-width: 1px 0px 0px 0px; /* 太さ */
   border-style: solid; /* 線種 */
   border-color: #D9D9D9;   /* 線色 */
   height: 1px;         /* 高さ(※古いIE用) */	
}
</style>

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
</head>
<script>
fbq('track', 'CompleteRegistration', {
value: 25.00,
currency: 'USD'
});
</script>
<body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

<!-- ##### RAKUTEN ##### -->
<?php
    if($_SESSION['currentasid'] == ASID_RAKUTEN) {
    echo "<div style=\"border:2px solid orange; padding:5px;\"><a href=".LINK_RAKUTEN."><img src=\"/common/img/raketenlogo.gif\" Align=\"left\" Hspace=\"10\" Vspace=\"0\"></a>こちらのページからは、ポケカルのサイトとなります。<br/>ご予約は「ポケカル」にて行われます。楽天スーパーポイントの対象外となりますので、あらかじめご了承ください。<br />&nbsp;<Input type=\"button\" value=\"楽天トラベルへ戻る\" onClick=\"location.href='".LINK_RAKUTEN."'\"><br /></div>";
}
?>
<!-- ##### /RAKUTEN ##### -->

<!-- ##### BODY ##### -->

<div class="main">
<!-- ##### BODY ##### -->
	<section>


		<h2 class="headline01 fs">
			イベント・ツアー予約(予約完了)
		</h2>
		
		<ol class="cart_stepbar">
			<li><span>1</span></li>
			<li><span>2</span></li>
			<li><span>3</span></li>
			<li><span>4</span></li>
			<li><span>5</span></li>
			<li class="visited"><span>6</span></li>
		</ol>
		
		<div class="carttableTop">
			<?php 
			if($cancelkiteikbn == 3){ 
			?>
				<div style="font-size:0.34rem; text-align: center; font-weight: bold;">
					お申込みいただき、ありがとうございました。
				</div>
			
				<div class="mb10"></div>
				
				<div>
					後日、ご登録いただいたメールアドレスに<span style="color:#DE0001;">「予約確認メール」</span>をお送りします。メールが届くまでお待ちください。
				</div>

			<?php 
			}else{ 
			?>

			<div style="font-size:0.34rem; text-align: center; font-weight: bold;">
				お申込みいただき、ありがとうございました。
			</div>
			
			<div class="mb10"></div>
			
			<div>
				ご登録いただいたメールアドレスに<span style="color:#DE0001;">「予約確認メール」</span>をお送りしました。確認メールが届かない場合、お手数ですが、「ポケカルお客様センター（03-5652-7072）」までご連絡ください。
			</div>

			<?php 
			} 
			?>
			
			<div class="mb5"></div>

			<?php
			if($isJCOM) {
			?>
			<p style="color:red">※予約確定メールは、当落通知メールではございません。<br />
			予約受付期間終了後、抽選を行い、改めて当落通知メールをお送りします。</p>
			<?php
			}
			?>			
			
		</div>
		
		<div class="mb10"></div>

		<h3 class="headline06">
			予約番号
		</h3>

		<div class="mb10"></div>
		<h4 class="headline07">
			予約番号
		</h4>
		<div class="carttableInfo">
			<div style="font-size:0.42rem;">
				<?php print(htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES));?>
			</div>
			<div class="mb5"></div>
			<div class="comment">
				<span style="color: #F00">
				※予約番号は、お客様のご予約内容を特定する際に必要となります。必ずお控えいただくようお願いいたします。
				</span>
			</div>
		</div>
		
		<div class="mb10"></div>
		<h4 class="headline07">
			予約日時
		</h4>
		<div class="carttableInfo">
			<?php print($bookingdt_y."年".$bookingdt_m."月".$bookingdt_d."日 ".$bookingdt_h.":".$bookingdt_min);?>
		</div>			
		
		<div class="mb10"></div>

		<h3 class="headline06">
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
		</h3>

		<div class="mb10"></div>
		<h4 class="headline07">
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
		</h4>
		<div class="carttableInfo">
			<?php print(htmlspecialchars($eventname, ENT_QUOTES));?>
		</div>

		<div class="mb10"></div>
		<h4 class="headline07">
			日付
		</h4>
		<div class="carttableInfo">
			<?php
			print(date('Y', $temp_day).'年');
			print(date('m', $temp_day).'月');
			print(date('d', $temp_day).'日(');
			setlocale(LC_TIME, 'ja_JP.UTF-8');
			print(strftime('%A', $temp_day).')');
			?>
			<?php
			if (!empty($_SESSION['thirdid'])) {
				print("<br />".htmlspecialchars($_SESSION['thirdid'], ENT_QUOTES));
			}
			?>
		</div>

		<div class="mb10"></div>
		<h4 class="headline07">
			申込人数
		</h4>
		<div class="carttableInfo">
			<div>
			大人　
			<span class="c-red">
			<?php
			//割引が適用されているか判断
			if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
				print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['baseadultfee'].")", ENT_QUOTES)."</font>");
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
			</div>

			<?php
			// 子供料金未設定の判断
			if (!$childfee_unsetflg) {
			?>
			<div class="mb5"></div>
			<div>
				子供　
				<span class="c-red">
					<?php
					//割引が適用されているか判断
					if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
						print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['basechildfee'].")", ENT_QUOTES)."</font>");
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
			</div>
			<?php
			}
			?>

			<?php
			//イベントカテゴリがチケットであれば送料発生
			if($_SESSION['postage'] != "" || $_SESSION['postage'] != null) {
			?>
				<div class="mb5"></div>
				<div class="comment">
				※当イベントはチケット送料が
				<?php
				print($_SESSION['postage']);
				?>
				円発生します。あらかじめご了承ください。
				</div>
			<?php
			}
			?>				

		</div>

		<?php
		// オプションがあるイベントか判断
		if (!$option_unsetflg) {
		print("<div class=\"mb10\"></div>");
		print("<h4 class=\"headline07\">選択肢</h4>");
		print("<div class=\"carttableInfo\">");
		// オプションありのイベントの場合
		//オプション数の上限までループ
			for($optcnt = 1; $optcnt < OPTSUBJECT + 1; $optcnt++){
				$optname = 'optname'.$optcnt;
				$optnum = 'optnum'.$optcnt;
				$optnamePLUS = 'optnum'.($optcnt+1);
				?>
				<p>
				<?php print(htmlspecialchars($_SESSION[$optname], ENT_QUOTES));?>
				<?php print(htmlspecialchars($_SESSION[$optnum], ENT_QUOTES));
				if($_SESSION[$optname] != ""):
				?>
				人
				<?php
				endif;
				?>
			</p> 
			<div class="mb5"></div>
		<?php                                                                                        
			}	
			print("</div>");
		}
		?>

		<hr/>
		<div class="mb40"></div>
		
		<div class="wrapper">

			<?php
			if(isset($_COOKIE['directref'])):
				if(strpos($_COOKIE['directref'],"dev-sp.pokekaruplus.net") || strpos($_COOKIE['directref'],"stg-sp.pokekaruplus.net") || strpos($_COOKIE['directref'],"sp.pokekaruplus.com")):

					if( strpos($_COOKIE['directref'],"dev-sp.pokekaruplus.net") ):
						$cam_url = "https://dev-sp.pokekaruplus.net/";
					elseif( strpos($_COOKIE['directref'],"stg-sp.pokekaruplus.net") ):
						$cam_url = "https://stg-sp.pokekaruplus.net/";
					elseif( strpos($_COOKIE['directref'],"sp.pokekaruplus.com") ):
						$cam_url = "https://sp.pokekaruplus.com/";
					else:
						$cam_url = "https://sp.pokekaruplus.com/";
					endif;
				?>
					<a href="<?php echo $cam_url; ?>" class="btn-detail">ポケカルプラスへ戻る</a>
				<?php
				else:
					?>
					<a href="/sp/" class="btn-detail">トップページへ</a>
					<?php
				endif;
			else:
				?>
				<a href="/sp/" class="btn-detail">トップページへ</a>
				<?php			
			endif;
			?>
		</div>		
		
	</section>
  


<!-- バナー広告 -->
<?php require_once 'bookend_banner.inc'; ?>
<!-- //バナー広告 -->


<!-- アフィリエイト広告 -->
<?php require_once 'bookend_affiliate.inc'; ?>
<!-- //アフィリエイト広告 -->


<!-- / .bottom -->
<!-- ##### /BODY ##### --> 
<!-- ##### TAG ##### -->
<div class="cvtag">
<?php
    echo $rttag_google;
    echo $rttag_blade;
    echo $tag_conv20121011;
    echo $tag_ydn_20130507;
    echo $tag_cv20131021;
	echo $tag_cv20160229;
?>

	<!-- 181226 ドルーグ -->
    <img src="https://afte.droog.ne.jp/action?c=292&x=304&m=<?php print(htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES));?>&t_id=<?php print(htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES));?>&a=1";; width="1" height="1" border="0">
</div><!-- cvtag -->
<!-- ##### /TAG ##### -->

</div>
<!-- / #page -->

<style>
.cvtag img{
	width:1px !important;
	height:1px !important;
}
</style>
<div class="cvtag">

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
printThunksTag('sp', $totalfee, $_SESSION['webbookingnumber'], $childfee_unsetflg)
?>




<div class="cvtag">
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
<!-- 171012追加 -->
<img src="https://t.afi-b.com/commit/C5317n/00116092" width="1" height="1" />
<script>(function(){var g = setInterval(function(){if(window["uAdBId"] === undefined){clearInterval(g);window["uAdBId"] = "C5317n";var a = document.createElement("script");a.async = 1;a.src = "https://t.afi-b.com/ath/ar.js";var b = document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a, b);}},10);})();</script><div id="imgC5317n"></div>

<span id="a8sales"></span>
<script>
a8sales({
  "pid": "s00000018007001",//後日、広告主様PGIDに変更して頂きますので、変更不可
  "order_number": "<?php echo htmlspecialchars($_SESSION['webbookingnumber'], ENT_QUOTES); ?>",//注文番号・現行タグの&so=の値を反映してください
  "currency": "JPY",//省略可
  "items": [　//以下、現行タグの&si=の値を反映してください
    {
      "code": "a8",//商品コード
      "price": 1,//固定値
      "quantity": 1,//固定値
    },
  ],
  "total_price": 1,//省略可、固定値
});
</script>
</div>

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
trk("configure", "tr_p3", "<?php echo (intval($_SESSION['adultfee']) + intval($_SESSION['childfee'])); ?>");
trk("configure", "tr_p4", "0");
trk("configure", "tr_p5", "0");
trk("configure", "tr_p6", "JPY");
trk("configure", "tr_p7", "1");
trk("configure", "tr_p8", "<?php if($_SESSION['paymethod'] == 'card') echo "1" ; else echo "3"; ?>");
trk("configure", "tr_p9", "<?php echo $_SESSION['eventid']; ?>");
trk("send", "41987");
</script>
<!-- CAW Tracking Conversion Code End -->
</div>
<?php require_once("2018/footer.html"); ?>