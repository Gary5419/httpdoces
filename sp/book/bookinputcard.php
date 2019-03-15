<?php
/**
 * 支払方法変更画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );
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
$screenid_before = SCREEN_ID_PREVIEW;
$filename_before = FILENAME_PREVIEW;

// 前画面_オーソリ終了画面
$screenid_before_card = SCREEN_ID_CARDRESULT;
$filename_before_card = FILENAME_CARDRESULT;

// 前画面_オーソリ終了(3D認証経由)画面
$screenid_before_card_3d = SCREEN_ID_CARDRESULT_3D;
$filename_before_card_3d = FILENAME_CARDRESULT_3D;

// 自画面
$screenid_current = SCREEN_ID_BOOKINPUT_CARD;
$filename_current = FILENAME_BOOKINPUT_CARD;

// 次画面
$screenid_next = SCREEN_ID_PREVIEW;
$filename_next = FILENAME_PREVIEW;

$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

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
    if ($_SESSION['screenid'] == $screenid_before || $_SESSION['screenid'] == $screenid_before_card || $_SESSION['screenid'] == $screenid_before_card_3d) {
        // 前画面チェックOK遷移
        $past_screen_ind = PAST_SCREEN_IND_BEFORE;
    } else {
        // 前画面チェックOK遷移以外
        $past_screen_ind = PAST_SCREEN_IND_OTHER;
    }
}

//特定のASID
$isJCOM = false;
if($_SESSION['currentasid'] == ASID_JCOM) {
  $isJCOM = true;
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

        // 旧予約番号をセット
        $bookingnumber = $_SESSION['bookingnumber'];

		$result_cd =  $_SESSION['result_cd'];
		$auth_result_cd = $_SESSION['$auth_result_cd'];

        //カード申込した予約データをキャンセル
        //予約データの取り消し
        $resbook = bookcancel($bookingnumber,$result_cd);

        // キャンセル結果判定
        if ($resbook == CANCEL_COMPLETE) {            // 結果OK

	        //データをセット
	        filterControlChar($_POST['paymethod']);
	        $_SESSION['paymethod'] = $_POST['paymethod'];
	        $_SESSION['resbook'] = BOOK_INITIALIZED;

	        //データ初期化
	        $_SESSION['bookingnumber'] = '';

            // 次画面にリダイレクト
            $locate = "Location: " .$url_currentdir.$filename_next;
            header("HTTP/1.0 301 Moved Permanently");
            header($locate);
            exit();
		} else {                                    // 結果NG
            // ログ出力メッセージ
            $error_msg = "予約キャンセル処理エラー発生";
            // ログ出力
            logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
            // もう一度この画面へリダイレクト
            $locate = "Location: " .$url_currentdir.FILENAME_BOOKINPUT_CARD;
            header("HTTP/1.0 301 Moved Permanently");
            header($locate);
            exit();
        }
    break;

    case PAST_SCREEN_IND_BEFORE:
        // 遷移元が前画面の場合

        // 操作開始時刻保存
        setOperationStartTime();

		//データ初期化
		$_SESSION['paymethod'] = '';

        //オーソリ結果をパラメータに保存
		//オーソリ結果をセット
		if ($_SESSION['auth_result'] == AUTH_NG) {
			$_SESSION['result_cd'] = $_SESSION['auth_result'];
		} else {
			$_SESSION['result_cd'] = AUTH_CANCEL;
		}

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

//オーソリNG申込の申し込みをキャンセルする
function bookcancel($bookingno,$auth_result) {

	global $mdbwww;
	global $mdbapp;

	//キャンセル対象のデータをAPPから抽出
	$sql1 = ' select  ';
	$sql1 .= ' id, ';
	$sql1 .= ' customer_id, ';
	$sql1 .= ' event_id, ';
	$sql1 .= ' stock_id, ';
	$sql1 .= ' account_method_type ';
	$sql1 .= ' from bookings where ';
	$sql1 .= ' id = ? ';
	//キャンセル対象の認証情報抽出SQLプリペア
	$stmt1 = $mdbapp->prepare($sql1, array('text'));
	if (PEAR::isError($stmt1)) {
		// ログ出力メッセージ
		$error_msg = "キャンセル対象の認証情報抽出SQLプリペアエラー:sql1";
		// ログ出力
		logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($sql1), __FILE__, __LINE__);
		// システムエラー画面にリダイレクト
		$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
		header("HTTP/1.0 301 Moved Permanently");
		header($locate);
		exit();
	}

	// キャンセル対象の認証情報抽出SQLプリペアドステートメント実行
	$res1 = $stmt1->execute(array($bookingno));
	if (PEAR::isError($res1)) {
		// ログ出力メッセージ
		$error_msg = "キャンセル対象の認証情報抽出SQLプリペアドステートメント実行エラー:sql1";
		// ログ出力
		logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($bookingno), __FILE__, __LINE__);
		// システムエラー画面にリダイレクト
		$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
		header("HTTP/1.0 301 Moved Permanently");
		header($locate);
		exit();
	}

	// キャンセル対象の予約情報抽出SQLフェッチ
	$row1 = $res1->fetchRow(MDB2_FETCHMODE_ASSOC);
	//データへセット
	$booking_id = $row1['id'];
	$customer_id = $row1['customer_id'];
	$event_id = $row1['event_id'];
	$stock_id = $row1['stock_id'];
	$account_method_type = $row1['account_method_type'];
    //キャンセルデータを配列にセット
    $bookingdata = array("from_web_flg" => WEB_APPLY_FLG
    ,"booking_id" => $booking_id
    ,"customer_id" => $customer_id
    ,"event_id" => $event_id
    ,"stock_id" => $stock_id
    ,"stock_box_id" => 1
    ,"medianame_id" => 25
    ,"medianumber_id" => 792
    ,"sel1_headcount" => 0
    ,"sel2_headcount" => 0
    ,"sel3_headcount" => 0
    ,"sel4_headcount" => 0
    ,"sel5_headcount" => 0
    ,"sel3_headcount" => 0
    ,"sel4_headcount" => 0
    ,"sel5_headcount" => 0
    ,"adult_headcount" => 0
    ,"child_headcount" => 0
    ,"status_id" => 13
	,"account_method_type" => APPCARD
    ,"dsk_count" => 0
    ,"gbk_count" => 0
    ,"covenant_count" => 0
    ,"contact_user_id" => 44
    ,"input_user_id" => 44
    ,"origin_type" => 6
    ,"incident_type_id" => 4);

	// ベーシック認証→キャンセルデータをPostで送信
	$res = basiccertify($bookingdata);
	//受け取った戻り値を仕分け
	$res = preg_split('/[{},:]/', $res);
	//実行結果の判定
	switch ($res[2]) {
		//キャンセル申込み完了
		case CANCEL_COMPLETE:
		break;
		//キャンセル申込み処理異常
		case CANCEL_FAILURE:
			// ログ出力メッセージ
			$error_msg = "APPシステムキャンセルデータ登録エラー";
			// ログ出力
			logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
			//エラーページへ遷移
			return CANCEL_FAILURE;
		break;
		//それ以外の異常系
		default :
			// ログ出力メッセージ
			$error_msg = "APPシステムエラー";
			// ログ出力
			logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
			//エラーページへ遷移
			return CANCEL_FAILURE;
		break;
	}
    // トランザクション開始
    $result = $mdbapp->beginTransaction();
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "トランザクション開始エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
        //エラーページへ遷移
        return CANCEL_FAILURE;
        break;
    }

	//APPオーソリ失敗ステータス更新
	//ステータスの更新_オーソリ(APP)
	$wrkdate = date("Y/m/d H:i:s");
	// SQL文
	$sql = 'UPDATE bookings SET ';
	$sql .= ' authstatus = "'.$auth_result.'"';
	$sql .= ' WHERE id = "'.$row1['id'].'"';
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
			//エラーページへ遷移
			return CANCEL_FAILURE;
			break;
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
			//エラーページへ遷移
			return CANCEL_FAILURE;
			break;
		}
	}

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
	$sql .= ' ,fromsalesforce = "'.FROM_APP_CANCEL_COMPLETE.'"';
	$sql .= ' ,fromsalesforcedate = "' .$wrkdate.'"';
	$sql .= ' ,tocustmermail = "'.TO_CUSTOMER_CANCEL_MAIL_COMPLETE.'"';
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
	return CANCEL_COMPLETE;
}

/**
 * 画面表示用DBアクセス
 */

// イベント名,キャンセル規定区分はSESSIONパラメータから取得
//     画面出力の際はサニタイズを忘れないこと。
$eventname      = $_SESSION['eventname'];
$cancelkiteikbn = $_SESSION['cancelkiteikbn'];
$thirdid   = $_SESSION['thirdid'];

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

$mdbwww->disconnect();
$mdbapp->disconnect();

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<title>クレジットカード支払い | ぽけかる倶楽部</title>
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
<script src="/sp/common/js/jquery.disableDoubleSubmit.js"></script>
<script>
$(function(){
  $("form").disableDoubleSubmit(3000);
});
</script>

<meta name="format-detection" content="telephone=no" />

<style>
	
hr{
   border-width: 1px 0px 0px 0px; /* 太さ */
   border-style: solid; /* 線種 */
   border-color: #D9D9D9;   /* 線色 */
   height: 1px;         /* 高さ(※古いIE用) */	
}
</style>
	  
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
			イベント・ツアー予約(予約内容入力)
		</h2>

		<ol class="cart_stepbar">
			<li class="visited"><span>1</span></li>
			<li><span>2</span></li>
			<li><span>3</span></li>
			<li><span>4</span></li>
			<li><span>5</span></li>
			<li><span>6</span></li>			
		</ol>
		
		<div class="carttableTop">
			<?php
			$tgtday = $_SESSION['saikouday'];
			$trandate = date("Y/m/d H:i:s");
			$calcdays = compareDate(substr($tgtday,0,4), substr($tgtday,4,2), substr($tgtday,6,2), substr($trandate,0,4), substr($trandate,5,2), substr($trandate,8,2));
			if ($calcdays >= DUEBASEDATE && $_SESSION['web_payment_type'] == "1") {

			}else{
			?>
				<div>WEBでの申込はクレジットカード支払のみとさせていただいております。それ以外の支払方法をご希望の方は、ポケカルお客様センターまでお電話ください。</div>
				<div>ポケカルお客様センター(9：00～18：00受付)<font color="blue"><b>03-5652-7072</b></font></div>
			<?php
			}
			?>
			<?php
			if (!cmIsEmpty($user_error)) {
				foreach ($user_error as $var) {
					print("$var<br/>");
				}
			}
			?>			
		</div>
		
		<form name="frm_booking" method="post" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
			<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
			
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
			
			<h3 class="headline06">
				ご連絡先	
			</h3>
			
			<div class="carttableInfo">
				<table class="tblUserInfoPrv">
					<tbody>
						<tr>
							<td>
								氏名<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['lastnamekanji'], ENT_QUOTES)."　".htmlspecialchars($_SESSION['firstnamekanji'], ENT_QUOTES));?>
							</td>
						</tr>
						<tr>
							<td>
								シメイ<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['lastnamekana'], ENT_QUOTES)."　".htmlspecialchars($_SESSION['firstnamekana'], ENT_QUOTES));?>				
							</td>
						</tr>
						<tr>
							<td>
								性別
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['gender'], ENT_QUOTES));?>				

							</td>
						</tr>
						<tr>
							<td>
								生年月日
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['birth_year'], ENT_QUOTES)."年".htmlspecialchars($_SESSION['birth_month'], ENT_QUOTES)."月".htmlspecialchars($_SESSION['birth_day'], ENT_QUOTES)."日");?>				

							</td>
						</tr>
						<tr>
							<td>
								郵便番号<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['postalcode1'], ENT_QUOTES));?>-<?php print(htmlspecialchars($_SESSION['postalcode2'], ENT_QUOTES));?>
							</td>
						</tr>
						<tr>
							<td>
								都道府県<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['address1'], ENT_QUOTES));?>
							</td>
						</tr>
						<tr>
							<td>
								市区<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['address2'], ENT_QUOTES));?>
							</td>
						</tr>
						<tr>
							<td>
								町村番地<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['address3'], ENT_QUOTES));?>
							</td>
						</tr>
						<tr>
							<td>
								電話番号<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['telephone1'], ENT_QUOTES));?>-<?php print(htmlspecialchars($_SESSION['telephone2'], ENT_QUOTES));?>-<?php print(htmlspecialchars($_SESSION['telephone3'], ENT_QUOTES));?>								
							</td>
						</tr>
						<tr>
							<td>
								Eメール<br>
							</td>
							<td>
								<?php print(htmlspecialchars($_SESSION['mailaddress'], ENT_QUOTES));?>								
							</td>
						</tr>
					</tbody>
				</table>
			</div>						
						
			<h3 class="headline08">
				<?php 
				if($isJCOM) { 
				?>
					当選後の
				<?php 
				} 
				?>
				キャンセルについて
			</h3>
			<div class="carttableInfo">
				<?php print($cancelrule);?>
			</div>	
			
			<?php 
			if($isJCOM) { 
			?>
				<h3 class="headline06">
					個人情報使用について
				</h3>
				<div class="carttableInfo">
					加入者認証の目的のみに使用することを同意する
				</div>
			<?php 
			} 
			?>
			
			<h3 class="headline06">
				メールマガジン
			</h3>
			<div class="carttableInfo">
				<?php print($_SESSION['mailmag'] == 'ok' ? '購読する' : '購読しない');?>
			</div>
			
			<?php
			$member_id = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : "";
			if($isJCOM && $member_id == "") { 
			?>
				<h3 class="headline06">
					情報誌
				</h3>
				<div class="carttableInfo">
				<?php 
					print($_SESSION['documentflg'] == 'ng' ? '発送不要' : '発送希望');
				?>
				</div>
			<?php 
			} 
			?>
			
			<h3 class="headline06">
				支払方法
			</h3>
			<div class="carttableInfo">
				<?php
				if (array_key_exists('paymethod', $_POST)) {
					$tmp_paymethod = $_POST['paymethod'];
				} else {
					$tmp_paymethod = "card";
				}
				?>
				<div class="checkpayment">
					<input type="radio" name="paymethod" value="card" id="paymethod-card" 
					<?php 
					if ($tmp_paymethod == "card") {
						print('checked="checked" ');
					}
					?>
					/>
					<label for="paymethod-card">クレジットカード</label>
					<?php
					$tgtday = $_SESSION['saikouday'];
					$trandate = date("Y/m/d H:i:s");
					$calcdays = compareDate(substr($tgtday,0,4), substr($tgtday,4,2), substr($tgtday,6,2), substr($trandate,0,4), substr($trandate,5,2), substr($trandate,8,2));
					if ($calcdays >= DUEBASEDATE && $_SESSION['web_payment_type'] == "1") {
						?>
						<input type="radio" name="paymethod" id="paymethod-discard" value="discard" 
						<?php
						if ($tmp_paymethod != "card") {
							print('checked="checked" ');
						}
						?> />
						<label for="paymethod-discard">払込票</label>
						<?php
					}else{
					?>
						<input type="radio" name="paymethod" id="paymethod-discard" value="" disabled />
						<label for="paymethod-discard"><font color="Silver">払込票</font></label>
					<?php
					}
					?>
				</div>
				<?php
				if ($calcdays >= DUEBASEDATE && $_SESSION['web_payment_type'] == "1") {
				}else{
				?>
					<div class="mb5"></div>
					<div>
						※本申込ではクレジットカードによるお支払のみを受け付けています。クレジットカード以外でのお支払をご希望の方は、ぽけかるコールセンターまでご連絡ください。
					</div>
					<div>
						ぽけかる倶楽部お客様センター(9：00～18：00受付)<font color="blue"><b>03-5652-7072</b></font>
					</div>
				<?php
				}
				?>
				<?php
				if($isJCOM) {
				?>
				<div class="mb5"></div>
				<div>
					※クレジットカード決済ご希望の方は、確認画面後に、カード会社の決済画面に移動します。カード情報の入力をしていただきますが、ご参加決定後の決済となりますのでご安心くださいませ。
				</div>
				<?php
				}
				?>
			</div>
			
			<p class="wrapper">
				<a href="#" class="btn-detail" onClick="document.frm_booking.submit();return false;">確認画面へ</a>
			</p>			
			
			
		</form>
		
	</section>

<!-- ##### /BODY ##### --> 

</div>
<!-- / #page -->

<?php require_once("2018/footer.html"); ?>