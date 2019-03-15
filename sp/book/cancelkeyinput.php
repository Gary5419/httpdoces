<?php
/**
 * 予約番号と確認キー入力画面
 * キャンセルを行うために、予約番号と確認キーの照合を行う
 *
 */

if(strpos($_SERVER['HTTP_HOST'],'master') === false){
    if (empty($_SERVER['HTTPS'])) {
        header("HTTP/1.0 301 Moved Permanently");
        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        exit;
    }
}

ini_set('display_errors', 'On');
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

// 自画面
$screenid_current = SCREEN_CANCEL_ID_KEYINPUT;
$filename_current = FILENAME_CANCEL_ID_KEYINPUT;

// 次画面
$screenid_next = SCREEN_CANCEL_PREVIEW;
$filename_next = FILENAME_CANCEL_PREVIEW;

$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

// DB接続(APP)
$mdbapp =& MDB2::connect(DB_DSN_APP);
if(PEAR::isError($mdbapp)) {
    // ログ出力メッセージ
    $error_msg = "DB接続エラー";
    // ログ出力
    logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, __FILE__, __LINE__);
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// DB接続(WWW)
$mdbwww =& MDB2::connect(DB_DSN_WWW);
if(PEAR::isError($mdbwww)) {
    // ログ出力メッセージ
    $error_msg = "DB接続エラー";
    // ログ出力
    logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, __FILE__, __LINE__);
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

//キャンセル対象の認証情報抽出SQL
$sql1 = ' select  ';
$sql1 .= ' web_booking_number, ';
$sql1 .= ' customer_id, ';
$sql1 .= ' account_method_type, ';
$sql1 .= ' title, ';
$sql1 .= ' thirdid, ';
$sql1 .= ' event_id, ';
$sql1 .= ' stock_id, ';
$sql1 .= ' saikou_day, ';
$sql1 .= ' sel1_headcount, ';
$sql1 .= ' sel2_headcount, ';
$sql1 .= ' sel3_headcount, ';
$sql1 .= ' sel4_headcount, ';
$sql1 .= ' sel5_headcount, ';
$sql1 .= ' adult_headcount, ';
$sql1 .= ' child_headcount, ';
$sql1 .= ' sel1_name, ';
$sql1 .= ' sel2_name, ';
$sql1 .= ' sel3_name, ';
$sql1 .= ' sel4_name, ';
$sql1 .= ' sel5_name, ';
$sql1 .= ' book_status, ';
$sql1 .= ' bookship_id ';
$sql1 .= ' from ';
$sql1 .= ' (select  ';
$sql1 .= ' b.id as book_id, ';
$sql1 .= ' web_booking_number, ';
$sql1 .= ' customer_id, ';
$sql1 .= ' account_method_type, ';
$sql1 .= ' title, ';
$sql1 .= ' thirdid, ';
$sql1 .= ' e.id as event_id, ';
$sql1 .= ' s.id as stock_id, ';
$sql1 .= ' stock_date as saikou_day, ';
$sql1 .= ' sel1_headcount, ';
$sql1 .= ' sel2_headcount, ';
$sql1 .= ' sel3_headcount, ';
$sql1 .= ' sel4_headcount, ';
$sql1 .= ' sel5_headcount, ';
$sql1 .= ' adult_headcount, ';
$sql1 .= ' child_headcount, ';
$sql1 .= ' sel1_name, ';
$sql1 .= ' sel2_name, ';
$sql1 .= ' sel3_name, ';
$sql1 .= ' sel4_name, ';
$sql1 .= ' sel5_name, ';
$sql1 .= ' status_id as book_status ';
$sql1 .= ' from bookings b,events e,stocks s where ';
$sql1 .= ' b.event_id = e.id and b.stock_id = s.id and ';
$sql1 .= ' b.id = ? and web_checkkey = ?) as bookinfo left join ';
$sql1 .= ' (select booking_id as bookship_id ';
$sql1 .= ' from booking_shippings where booking_id = ? group by booking_id) as ship ';
$sql1 .= ' on bookinfo.book_id = ship.bookship_id ';
//キャンセル対象の認証情報抽出SQLプリペア
$stmt1 = $mdbapp->prepare($sql1, array('text', 'text', 'text'));
if (PEAR::isError($stmt1)) {
    // ログ出力メッセージ
    $error_msg = "キャンセル対象の認証情報抽出SQLプリペアエラー:sql1";
    // ログ出力
    logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($sql1), __FILE__, __LINE__);
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

//キャンセル対象の予約情報抽出SQL
$sql2 = ' select ';
$sql2 .= ' mailaddress ';
$sql2 .= ' ,lastnamekanji ';
$sql2 .= ' ,firstnamekanji ';
$sql2 .= ' ,lastnamekana ';
$sql2 .= ' ,firstnamekana ';
$sql2 .= ' ,postalcode ';
$sql2 .= ' ,address1 ';
$sql2 .= ' ,address2 ';
$sql2 .= ' ,address3 ';
$sql2 .= ' ,telephone ';
$sql2 .= ' from d_booking ';
$sql2 .= ' where poke_app_bookingnumber = ? And ';
$sql2 .= ' checkkey = ? ';
//キャンセル対象の予約情報抽出SQLプリペア
$stmt2 = $mdbwww->prepare($sql2, array('text', 'text'));
if (PEAR::isError($stmt2)) {
    // ログ出力メッセージ
    $error_msg = "キャンセル対象の予約情報抽出SQLプリペアエラー:sql2";
    // ログ出力
    logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($sql2), __FILE__, __LINE__);
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

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
	$past_screen_ind = PAST_SCREEN_IND_BEFORE;
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

	$cancel_error = $_SESSION['cancel_error'];

        // session画面IDに自画面IDをセット
        $_SESSION['screenid'] = $screenid_current;
		// 予約番号と確認キーを認証失敗を何回したか
		if($cancel_error > CERTIFY_LIMIT){
			//コールセンター画面へ電話してください画面へ
            $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
            header("HTTP/1.0 301 Moved Permanently");
            header($locate);
            exit();
		}
        // 自画面からのデータの入力チェック
        checkPostData($user_error);
        // チェック判定
        if (cmIsEmpty($user_error)) {    // チェックOK
            // 入力データを格納
            storePostData();

			//予約キャンセルのための認証処理
			//予約番号と確認キーを条件に抽出
		    // キャンセル対象の認証情報抽出SQLプリペアドステートメント実行
		    $res1 = $stmt1->execute(array($_POST['bookingnumber'],$_POST['cancelcheckkey'],$_POST['bookingnumber']));
		    if (PEAR::isError($res1)) {
		        // ログ出力メッセージ
		        $error_msg = "キャンセル対象の認証情報抽出SQLプリペアドステートメント実行エラー:sql1";
		        // ログ出力
		        logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($row1['bookingnumber']), __FILE__, __LINE__);
    			// システムエラー画面にリダイレクト
    			$locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
    			header("HTTP/1.0 301 Moved Permanently");
    			header($locate);
		        exit();
		    }
		    // キャンセル対象の予約情報抽出SQLフェッチ
		    $row1 = $res1->fetchRow(MDB2_FETCHMODE_ASSOC);
		    if (is_null($row1)) {
				// エラーメッセージをセットして自画面表示
				$user_error[] = '予約番号か確認キーが違います';
				$cancel_error++;
				$_SESSION['cancel_error'] = $cancel_error;
				break;
		    }
			//予約ステータスチェック_キャンセル済はNG
			if ($row1[book_status] == CANCEL_END) {
				//コールセンター画面へ電話してください画面へ
				$locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
				header("HTTP/1.0 301 Moved Permanently");
				header($locate);
				exit();
			}
			//支払い方法チェック_現金払いはNG
			if ($row1[account_method_type] == APPCASH) {
				//コールセンター画面へ電話してください画面へ
	            $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
			}

			//支払方法チェック_カード払いはNG
			if ($row1[account_method_type] == APPCARD) {
				//コールセンター画面へ電話してください画面へ
				$locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
				header("HTTP/1.0 301 Moved Permanently");
				header($locate);
				exit();
			}

			//払込票が発送されていないか
			if ($row1[bookship_id] != NULL) {
				//コールセンター画面へ電話してください画面へ
	            $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
			}

		$stmt1->free();
			//DB切断
    		$mdbapp->disconnect();

    		// キャンセル対象の予約情報抽出SQLプリペアドステートメント実行
    		$res2 = $stmt2->execute(array($_POST['bookingnumber'],$_POST['cancelcheckkey']));
    			if (PEAR::isError($res2)) {
        		// ログ出力メッセージ
        		$error_msg = "キャンセル対象の予約情報抽出SQLプリペアドステートメント実行エラー:sql2";
        		// ログ出力
        		logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($_POST['bookingnumber']), __FILE__, __LINE__);
                        // システムエラー画面にリダイレクト
                        $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ERROR;
                        header("HTTP/1.0 301 Moved Permanently");
                        header($locate);
        		exit();
    		}

    		// フェッチ
    		$row2 = $res2->fetchRow(MDB2_FETCHMODE_ASSOC);
    		if (is_null($row2)) {
        		// ログ出力メッセージ
        		$error_msg = "キャンセル対象の予約情報抽出SQLフェッチエラー:sql2";
        		// ログ出力
        		logOnline($error_msg, BATCH_LOG_LEVEL_FATAL, array($_POST['bookingnumber']), __FILE__, __LINE__);
                        //コールセンター画面へ電話してください画面へ
                    	$locate = "Location: " .$url_currentdir.FILENAME_CANCEL_CALL;
                    	header("HTTP/1.0 301 Moved Permanently");
                    	header($locate);
                    	exit();
			}
		//セッションデータへセット
		$_SESSION['appbookingnumber'] = $_POST['bookingnumber'];
		$_SESSION['cancelcheckkey'] = $_POST['cancelcheckkey'];
        $_SESSION['title'] = $row1['title'];
		$_SESSION['webbookingnumber'] = substr(str_repeat('0', 8).$row1['web_booking_number'], -8, 8);
        $_SESSION['customer_id'] = $row1['customer_id'];
		$_SESSION['mailaddress'] = $row2['mailaddress'];
		$_SESSION['eventid'] = $row1['event_id'];
        $_SESSION['stockid'] = $row1['stock_id'];
		$_SESSION['saikouday'] = $row1['saikou_day'];
		$_SESSION['thirdid'] = $row1['thirdid'];
		$_SESSION['mannum'] = $row1['adult_headcount'];
		$_SESSION['boynum'] = $row1['child_headcount'];
		$_SESSION['sel1_name'] = $row1['sel1_name'];
		$_SESSION['sel1_headcount'] = $row1['sel1_headcount'];
		$_SESSION['sel2_name'] = $row1['sel2_name'];
		$_SESSION['sel2_headcount'] = $row1['sel2_headcount'];
		$_SESSION['sel3_name'] = $row1['sel3_name'];
		$_SESSION['sel3_headcount'] = $row1['sel3_headcount'];
		$_SESSION['sel4_name'] = $row1['sel4_name'];
		$_SESSION['sel4_headcount'] = $row1['sel4_headcount'];
		$_SESSION['sel5_name'] = $row1['sel5_name'];
		$_SESSION['sel5_headcount'] = $row1['sel5_headcount'];
		$_SESSION['lastnamekanji'] = $row2['lastnamekanji'];
		$_SESSION['firstnamekanji'] = $row2['firstnamekanji'];
		$_SESSION['lastnamekana'] = $row2['lastnamekana'];
		$_SESSION['firstnamekana'] = $row2['firstnamekana'];
		$_SESSION['postalcode'] = $row2['postalcode'];
		$_SESSION['address1'] = $row2['address1'];
		$_SESSION['address2'] = $row2['address2'];
		$_SESSION['address3'] = $row2['address3'];
		$_SESSION['telephone'] = $row2['telephone'];

			$stmt2->free();
			//DB切断
			$mdbwww->disconnect();

			// 次画面にリダイレクト
			$locate = "Location: " .$url_currentdir.$filename_next;
			header("HTTP/1.0 301 Moved Permanently");
			header($locate);
			exit();
        }
        break;

    case PAST_SCREEN_IND_BEFORE:
        // 遷移元が前画面の場合
		//エラーフラグ初期化
		$cancel_error =0;
		$_SESSION['cancel_error'] = $cancel_error;
        // 操作開始時刻保存
        setOperationStartTime();

        break;

    default:
        // 遷移元がその他の場合

        // 不正操作エラー画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_CANCEL_ILLEGAL;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
        exit();

        break;
}

/**
 * データ入力チェック
 */
function checkPostData(&$user_error) {
    // 予約管理番号/bookingnumber/必須/10
    isParamCheckOK($user_error, $_POST['bookingnumber'], '予約番号', true, '20', '1', '', '^[0-9]+$');
    // キャンセル確認キー/cancelcheckkey/必須/10
    isParamCheckOK($user_error, $_POST['cancelcheckkey'], '確認キー', true, 10, 10, '', '^[0-9]+$');
}

/**
 * データ格納
 */
function storePostData() {
    $_SESSION['bookingnumber'] = $_POST['bookingnumber'];
    $_SESSION['cancelcheckkey'] = $_POST['cancelcheckkey'];
}


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
<title>ツアー・イベントのキャンセル | ポケカル</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="./css/style.css?v=1" />

    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("tags/head_tag.php"); ?>


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/sp/common/js/flipsnap.min.js"></script>



<meta name="format-detection" content="telephone=no" />

<link rel="alternate" media="only screen and (max-width: 640px)" href="http://www.poke.co.jp/sp/bustours/">
<link rel="canonical" href="http://www.poke.co.jp/bustours/">
</head>

  <body>
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

      <div class="main">
        <ul class="breadcrumb">
          <li><a href="../">日帰り旅行・ツアーTOP</a></li>
		  <li class="pl20">ツアー・イベントのキャンセル</li>
        </ul>
        <section>

<h1 class="headline01">ツアー・イベントのキャンセル</h1>





    
<div class="mb30"></div>

        <section id="">


<ol class="cancel_stepbar">
<li class="visited"><span>1</span><br>番号入力</li>
<li><span>2</span><br>内容確認</li>
<li><span>3</span><br>完了</li>
</ol>

	<div class="mb20"></div>

<div class="mlr15">			
				
				<p class="rem03">
				予約完了時にお届けしたメール本文内の「予約番号」と「確認キー」を入力して[入力内容確認]を押してください。
				<br>
				<span class="c_dd0000">※クレジットカードで決済されたお客様につきましては、当キャンセルフォームからキャンセル手続きはできません。お手数ではございますが、ポケカルお客様センター(03-5652-7072)へご連絡ください。</span>
				</p>
</div>
                                <?php
                                // エラーメッセージ出力
                                if (!cmIsEmpty($user_error)) {
                                        print("<ul id=\"error-list\">");
                                    foreach ($user_error as $var) {
                                        print("<li>$var</li>");
                                    }
                                        print("</ul>");
                                }
                                ?>
                            <form name="cancelform" method="post" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
                                    <input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
                                    
                <div class="mb50"></div>		
                
				<div class="panel mlr15">
                <div class="panel-body canceltable">
					<table>
                    	<tr>
                            <th>予約番号</th>
                            <td>
                                <input class="form-input" type="text" name="bookingnumber" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" style="ime-mode: disabled;-webkit-appearance: none !important;border-radius:0;"
                                                                    <?php
                                                                    if (array_key_exists('bookingnumber', $_POST)) {
                                                                        print('value="'.htmlspecialchars($_POST['bookingnumber'], ENT_QUOTES).'" ');
                                                                    }elseif( isset($_SESSION['bookingnumber']) ){
																		print('value="'.htmlspecialchars($_SESSION['bookingnumber'], ENT_QUOTES).'" ');
																	}
                                                                    ?>
                                                                    >
							<div class="rem028">6桁の番号を入力ください</div>
                            </td>
                        </tr>
                        <tr>
                            <th>確認キー</th>
                            <td>
                                <input class="form-input" type="text" name="cancelcheckkey" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" style="ime-mode: disabled;-webkit-appearance: none !important;border-radius:0;"
                                                                    <?php
                                                                        if (array_key_exists('cancelcheckkey', $_POST)) {
                                                                            print('value="'.htmlspecialchars($_POST['cancelcheckkey'], ENT_QUOTES).'" ');
                                                                        }elseif( isset($_SESSION['cancelcheckkey']) ){
																			print('value="'.htmlspecialchars($_SESSION['cancelcheckkey'], ENT_QUOTES).'" ');
																		}
                                                                    ?>
                                                                    >
							<div class="rem028">10桁の番号を入力ください</div>
                            </td>
                        </tr>
					</table>
				</div>
                </div>


               
                <div class="mb30"></div>
				
				
				<div class="mlr15">
                <p class="btn-detail"><a href="javascript:void(0);" class="btn-detail" onclick="document.cancelform.submit();">入力内容確認</a></p>
                
				</div>
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