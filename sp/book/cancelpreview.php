<?php
/**
 * キャンセル内容確認画面
 *
 *
 */
require_once("include/2018/config.php");

#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/common.inc.php';
#require 'include/pokebook.inc.php';
#require 'include/template_book.inc.php';

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
$screenid_before = SCREEN_CANCEL_ID_KEYINPUT;
$filename_before = FILENAME_CANCEL_ID_KEYINPUT;

// 自画面
$screenid_current = SCREEN_CANCEL_PREVIEW;
$filename_current = FILENAME_CANCEL_PREVIEW;

// 次画面
$screenid_next = SCREEN_CANCEL_END;
$filename_next = FILENAME_CANCEL_END;

$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');
// DB接続
$mdbwww =& MDB2::connect(DB_DSN_WWW);
if(PEAR::isError($mdb2)) {
    // ログ出力メッセージ
    $error_msg = "DB接続エラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}
// 催行日フォーマット変更
$ss_day = str_replace("-","",$_SESSION['saikouday']);
$temp_day = mktime(0, 0, 0, substr($ss_day, 4, 2), substr($ss_day, 6, 2), substr($ss_day, 0, 4));
$cond_day = date('Y-m-d', $temp_day);

// ロックファイル名
$lockfilename = LOCKFILE_DIRECTORY.LOCKFILE_PREFIX.session_id();

//キャンセルフラグ初期化
$_SESSION['resbook'] = CANCEL_INITIALIZED;

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

        do {
            // ロックファイルオープン
            $fp = fopen($lockfilename, 'w');

            // 排他ロック
            // ロック待ちを flock にまかせるため、LOCK_NB は指定しない。
            $retlock = flock($fp, LOCK_EX);
            if (!$retlock) {
                // ロック待ちを自分で制御する場合はここに記述
            }

            // 負荷軽減のためのSLEEP
            //usleep(100000);

        // 排他ロック失敗の場合ループ
        } while (!$retlock);
	        // キャンセル申込みフラグが初期値の場合のみキャンセル申込み処理実行
	        if ($_SESSION['resbook'] == CANCEL_INITIALIZED) {

	            if (0) {
	                // 2度押し対応確認用コード
	                sleep(5);
	            }

	            // キャンセル申込
	            $resbook = bookcancel($system_error, $bookingdt);
	            // キャンセル結果をセッションパラメータに保存
	            $_SESSION['resbook'] = $resbook;
	            // キャンセル申込日時(Unixタイムスタンプ)をセッションパラメータに保存
	            $_SESSION['bookingdt'] = $bookingdt;
	        }

	        // ロックファイルクローズ
	        fclose($fp);

	        // キャンセル結果判定
	        if ($_SESSION['resbook'] == CANCEL_COMPLETE) {            // 結果OK
	            // 各処理
	            // 次画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.$filename_next;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
	        } elseif ($_SESSION['resbook'] == CANCEL_FAILURE) {  // 結果NG
	            // コールセンター架電要求画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
			    header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
	        } else {                                    // 結果NG
	            // ログ出力メッセージ
	            $error_msg = "予約キャンセル処理エラー発生";
	            // ログ出力
	            logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

	            // システムエラー画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
	        }

        break;

    case PAST_SCREEN_IND_BEFORE:
        // 遷移元が前画面の場合
        // 操作開始時刻保存
        setOperationStartTime();

        break;

    default:
        // 遷移元がその他の場合

        // 不正操作画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ILLEGAL;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
        exit();

        break;

}

/**
 * キャンセル申込
 */
function bookcancel(&$system_error, &$bookingdt) {

    global $cond_day;
    global $mdbwww;

    // トランザクション開始
    $result = $mdbwww->beginTransaction();
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "トランザクション開始エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
        return CANCEL_FAILURE;
    }
	//ステータスの更新_APPへ送信
	$wrkdate = date("Y/m/d H:i:s");
	// SQL文
	$sql = 'UPDATE d_booking SET ';
	$sql .= ' tosalesforce = "'.TO_APP_CANCEL_COMPLETE.'"';
	$sql .= ' ,tosalesforcedate = "' .$wrkdate.'"';
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
		return CANCEL_FAILURE;
	}
	//キャンセルデータを配列にセット
	$bookingdata = array("from_web_flg" => WEB_APPLY_FLG
		,"booking_id" => $_SESSION['appbookingnumber']
        ,"customer_id" => $_SESSION['customer_id']
		,"event_id" => $_SESSION['eventid']
		,"stock_id" => $_SESSION['stockid']
		,"stock_box_id" => 1
		,"medianame_id" => 25
		,"medianumber_id" => 209
		,"sel1_headcount" => 0
		,"sel2_headcount" => 0
		,"sel3_headcount" => 0
		,"sel4_headcount" => 0
		,"sel5_headcount" => 0
		,"adult_headcount" => 0
		,"child_headcount" => 0
		,"status_id" => 13
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
			// ロールバック
			$ret = $mdbwww->rollback();
			if (PEAR::isError($ret)) {
				// ログ出力メッセージ
				$error_msg = "ロールバックエラー";
				// ログ出力
				logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
			}
			//エラーページへ遷移
			return CANCEL_FAILURE;
			break;
		//それ以外の異常系
		default :
			// ログ出力メッセージ
			$error_msg = "APPシステムエラー";
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
			//エラーページへ遷移
			return CANCEL_FAILURE;
			break;
	}

	//ステータスの更新_APPから受信
	$wrkdate = date("Y/m/d H:i:s");
	// SQL文
	$sql = 'UPDATE d_booking SET ';
	$sql .= ' fromsalesforce = "'.FROM_APP_CANCEL_COMPLETE.'"';
	$sql .= ' ,fromsalesforcedate = "' .$wrkdate.'"';
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
		return CANCEL_FAILURE;
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
        return CANCEL_FAILURE;
    }

    return CANCEL_COMPLETE;
}

/**
 * 画面表示用DBアクセス
 */

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
if (is_null($_SESSION['sel1_headcount'])) {
    // オプション１がNULLの場合
    $option_unsetflg = TRUE;
}

$mdbwww->disconnect();

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="Description" content="お申込みいただいたツアー・イベントのキャンセルはこちらのページからお願いします。" />
<meta name="Keywords" content="キャンセル,日帰り,バスツアー,ツアー,旅行,ポケカル" />
<meta name="copyright" content="" />
<title>ツアー・イベントのキャンセル(内容確認) | ポケカル</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="./css/style.css" />

    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("/_data/tags/head_tag.php"); ?>


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/sp/common/js/flipsnap.min.js"></script>



<meta name="format-detection" content="telephone=no" />

<link rel="alternate" media="only screen and (max-width: 640px)" href="http://www.poke.co.jp/sp/bustours/">
<link rel="canonical" href="http://www.poke.co.jp/bustours/">
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
		  <li class="pl20">ツアー・イベントのキャンセル(内容確認)</li>
        </ul>
        <section>

<h1 class="headline01">ツアー・イベントのキャンセル</h1>
            
            
<div class="mb50"></div>

        <section id="mainField">

<ol class="cancel_stepbar">
<li class="visited"><span>1</span><br>番号入力</li>
<li class="visited"><span>2</span><br>内容確認</li>
<li><span>3</span><br>完了</li>
</ol>

<div class="mb20"></div>

<div class="mlr15">			
				
				<p class="rem03">
				<span class="c_dd0000">(！) 下記のご予約をキャンセルされますか？「予約をキャンセルする」ボタンを押されますと、対象ツアー・イベントのキャンセルが完了します。キャンセル完了後、「キャンセル完了メール」が自動送信されます。メールが受信できない場合は、ポケカルお客様センター(03-5652-7072)までご連絡ください。</span>
				</p>
</div>
				
				
				<form name="cancelform" method="post" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
                                <input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
				
                <div class="mb50"></div>
                
				<div class="panel mlr10">
                <div class="panel-body canceltable">
					<table>
                        <tr>
                            <th>予約番号</th>
                            <td>
                                　<?php print(htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES)); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>確認キー</th>
                            <td>
                                　<?php print(htmlspecialchars($_SESSION['cancelcheckkey'], ENT_QUOTES)); ?>
                            </td>
                        </tr>
					</table>
				</div>
                
                <div class="mb30"></div>
				
				
                <a href="<?php echo $url_currentdir.FILENAME_CANCEL_ID_KEYINPUT; ?>" class="btn-detail">前の画面に戻る</a>
                
                <div class="mb10"></div>
                
                <a href="#" class="btn-detail" onclick="document.cancelform.submit();">予約をキャンセルする</a>
                
                   
					
				
				
				</form>
                
                <div class="mb100"></div>


        <!-- ##### /MAIN ##### -->
      </div>
    </div>
  </div>
  <!-- ##### /BODY ##### --> 

</div>

</section>
       <?php require_once("2018/footer.html"); ?>