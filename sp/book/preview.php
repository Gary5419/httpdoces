<?php
/**
 * 予約確認画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );
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
$screenid_before = SCREEN_ID_BOOKINPUT;
$filename_before = FILENAME_BOOKINPUT;
$screenid_before_card = SCREEN_ID_BOOKINPUT_CARD;
$filename_before_card = FILENAME_BOOKINPUT_CARD;

// 自画面
$screenid_current = SCREEN_ID_PREVIEW;
$filename_current = FILENAME_PREVIEW;

// 次画面
$screenid_next = SCREEN_ID_END;
$filename_next = FILENAME_END;

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
    if ($_SESSION['screenid'] == $screenid_before) {
        // 前画面チェックOK遷移
        $past_screen_ind = PAST_SCREEN_IND_BEFORE;
    } elseif ($_SESSION['screenid'] == $screenid_before_card) {
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

		//確認キー画面非表示対応
		if (CHECKKEYUSEFLG == 1) {

	        // 確認キー有効チェック(タイムアウトの確認)
	        $res = validateCheckKey($_SESSION['mailaddress'], $_SESSION['checkkey'], $error);

	        if ($res == VALIDATE_CHECKKEY_VALID) {          // 確認キー正常
	            // 何もせず次の処理へ
	        } elseif ($res == VALIDATE_CHECKKEY_INVALID) {  // 確認キー異常
	            // ログ出力メッセージ
	            $error_msg = "確認キー不正";
	            // ログ出力
	            logOnline($error_msg, ONLINE_LOG_LEVEL_ERROR, __FILE__, __LINE__);

	            // システムエラー画面にリダイレクト(この画面で異常はありえない)
	            $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
	        } elseif ($res == VALIDATE_CHECKKEY_EXPIRE) {   // 確認キー失効
	            // タイムアウト画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.FILENAME_TIMEOUT;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
	        } else {                                        // 確認キーチェック処理エラー
	            // ログ出力メッセージ
	            $error_msg = "確認キーチェック処理エラー発生";
	            // ログ出力
	            logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

	            // システムエラー画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	            header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
			}
		}
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
	        // 予約申込みフラグが初期値の場合のみ予約申込み処理実行
	        if ($_SESSION['resbook'] == BOOK_INITIALIZED) {

	            if (0) {
	                // 2度押し対応確認用コード
	                sleep(5);
	            }

	            // 予約申込
	            $resbook = book($system_error, $bookingdt);
	            // 予約結果をセッションパラメータに保存
	            $_SESSION['resbook'] = $resbook;
	            // 予約申込日時(Unixタイムスタンプ)をセッションパラメータに保存
	            $_SESSION['bookingdt'] = $bookingdt;
	        }
	        // ロックファイルクローズ
	        fclose($fp);
	        // 申込結果判定
	        if ($_SESSION['resbook'] == BOOK_COMPLETE) {            // 結果OK
				// 各処理
				//支払方法がカードか
				if ($_SESSION['paymethod'] == 'discard' || $_SESSION['paymethod'] == 'cash') {  //カード以外
					// 次画面にリダイレクト
					$locate = "Location: " .$url_currentdir.$filename_next;
					header("HTTP/1.0 301 Moved Permanently");
					header($locate);
					exit();
				} elseif ($_SESSION['paymethod'] == 'card') {       //カード
					//約款確認画面へ
					$locate = "Location: " .$url_currentdir.FILENAME_BOOKAGREE;
					header("HTTP/1.0 301 Moved Permanently");
					header($locate);
					exit();
				} else {
					// ログ出力メッセージ
					$error_msg = "申込処理エラー発生_支払方法が不明";
					// ログ出力
					logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

					// システムエラー画面にリダイレクト
					$locate = "Location: " .$url_currentdir.FILENAME_ERROR;
					header("HTTP/1.0 301 Moved Permanently");
					header($locate);
					exit();
				}
	        } elseif ($_SESSION['resbook'] == BOOK_OUT_OF_STOCK) {  // 在庫不足
	            // 予約不可画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.FILENAME_BOOKFULL;
			    header("HTTP/1.0 301 Moved Permanently");
	            header($locate);
	            exit();
	        } else {                                    // 結果NG
	            // ログ出力メッセージ
	            $error_msg = "申込処理エラー発生";
	            // ログ出力
	            logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);

	            // システムエラー画面にリダイレクト
	            $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
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
		$locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL;
		header("HTTP/1.0 301 Moved Permanently");
		header($locate);
		exit();

	break;

}

/**
 * 予約申込
 */
function book(&$system_error, &$bookingdt) {

    global $cond_day;
    global $mdbwww;
    global $mdbapp;
    global $isJCOM;

	if ($_SESSION['paymethod'] == 'card') {
		$duemethod = DUECARD;
		$account_method_type = APPCARD;
		$dsk_count = NULL;
                $gbk_count = NULL;
                $covenant_count = NULL;
	} elseif ($_SESSION['paymethod'] == 'discard') {
		//払い込み票使用
		$duemethod = DUEDENSAN;
		$account_method_type = APPDENSAN;
		$dsk_count = 1;
                $gbk_count = 1;
                $covenant_count = 1;
	} elseif ($_SESSION['paymethod'] == 'cash') {
		//当日現金扱い
		$duemethod = DUECASH;
		$account_method_type = APPCASH;
		$dsk_count = NULL;
                $gbk_count = NULL;
                $covenant_count = NULL;
	} else {
		// ログ出力メッセージ
		$error_msg = "支払方法が不明";
		// ログ出力
		logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, $_SESSION['paymethod'], __FILE__, __LINE__);
		return BOOK_FAILURE;
	}

	$_SESSION['duemethod'] = $duemethod;

	//合計料金を計算
	$calcres = 0;
	$basecalcres = 0;

	//大人計算
	$calcres = $calcres + htmlspecialchars($_SESSION['adultfee'] * $_SESSION['mannum']);
	$basecalcres = $basecalcres + htmlspecialchars($_SESSION['baseadultfee'] * $_SESSION['mannum']);
	//子供計算
	$calcres = $calcres + htmlspecialchars($_SESSION['childfee'] * $_SESSION['boynum']);
	$basecalcres = $basecalcres + htmlspecialchars($_SESSION['basechildfee'] * $_SESSION['boynum']);
	$_SESSION['calcres'] = $calcres;
	$_SESSION['basecalcres'] = $basecalcres;

	//送料計算
	if($_SESSION['postage'] != "" || $_SESSION['postage'] != null) {
		$_SESSION['calcres'] = $calcres + $_SESSION['postage'];
		$_SESSION['basecalcres'] = $basecalcres + $_SESSION['postage'];
	} else {
		$_SESSION['calcres'] = $calcres;
		$_SESSION['basecalcres'] = $basecalcres;
	}

    //割引金額を算出
	if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
		$adult_discount_rate = htmlspecialchars($_SESSION['baseadultfee'] - $_SESSION['adultfee']);
		$child_discount_rate = htmlspecialchars($_SESSION['basechildfee'] - $_SESSION['childfee']);
	}
        
        
        if($_SESSION['auto_furiwake'] == 1){
            //まとめ表示の処理
            //共有在庫数チェック
            $ret_share = checkShareHeadcount($wait_headcount);
            if ($ret_share != 1){
                return $ret_share;
            }
            //共有在庫内にキャンセル待ちがいたら満席
            if($wait_headcount > 0){
                //満席
                return BOOK_OUT_OF_STOCK;
            }
            
            $ret = CheckShareStockInfo_SameEventId($_SESSION['stock_id']);
            //同一イベントIDで共有在庫が2件以上存在する
            if(is_array($ret)){
                if(count($ret) > 1){
                    $is_full = true;
                    for($i=0;$i<count($ret);$i++) {
                        // 在庫データ－既申込者数(キャンセル待ちの人数含む)
                        $zaikocount = $ret[$i]['poke_max_headcount'] - ($ret[$i]['poke_curr_headcount'] + $ret[$i]['poke_wait_headcount']);
                        //同一セッション内申込者数
                        $totalnum = $_SESSION['mannum'] + $_SESSION['womannum'] + $_SESSION['boynum'] + $_SESSION['girlnum'];

                        // 予約可能、在庫引当
                        if ($totalnum <= $zaikocount) {
                            $_SESSION['stock_id']=$ret[$i]['stock_id'];
                            $is_full = false;
                            break;
                        }
                    }
                    if($is_full){
                        //満席
                        return BOOK_OUT_OF_STOCK;
                    }
                }else{
                    // ログ出力メッセージ
                    $error_msg = "まとめ表示共有件数エラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, '', __FILE__, __LINE__);
                    return BOOK_FAILURE;
                }
            }else{
                // ログ出力メッセージ
                $error_msg = "まとめ表示共有件数エラー";
                // ログ出力
                logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, '', __FILE__, __LINE__);
                return BOOK_FAILURE;
            }
        }else{
            //個別表示の処理
            // 在庫イベントのステータスの確認
        // SQL文
            $sql = "SELECT judgement as uketukestatus,poke_max_headcount as max_headcount FROM ".APPDB.".stocks sto ";
            $sql .= " WHERE id = ?";

            // プリペア
            $stmt = $mdbapp->prepare($sql, array('text'));
            if (PEAR::isError($stmt)) {
                    // ログ出力メッセージ
                    $error_msg = "在庫検索SQLプリペアエラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            }

            // SQL実行
            $result = $stmt->execute(array($_SESSION['stock_id']));
            if (PEAR::isError($result)) {
                    // ログ出力メッセージ
                    $error_msg = "在庫検索SQL実行エラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid'], $cond_day), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            }
            // フェッチ
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            if (is_null($row)) {
                    // ログ出力メッセージ
                    $error_msg = "該当する在庫がありません。";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid'], $cond_day), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            } else {
                    $uketukestatus = $row['uketukestatus'];
                    if ($uketukestatus != SAIKOU_HANDAN_NOTFIX and $uketukestatus != SAIKOU_HANDAN_FIX) {   // 開催決定・開催未定以外
                            // 受付不可返却
                            return BOOK_OUT_OF_STOCK;
                    }
            }
            $stmt->free();
            $max_headcount = $row['max_headcount'];

            //共有在庫数チェック
            $ret = checkShareHeadcount();
            if ($ret != 1){
                return $ret;
            }

            // 在庫イベントの確認→キャンセル待ちがいたら在庫切れとする
            // SQL文
            $sql = "SELECT poke_wait_headcount FROM ".APPDB.".stocks sto ";
            $sql .= " WHERE id = ?";

            // プリペア
            $stmt = $mdbapp->prepare($sql, array('text'));
            if (PEAR::isError($stmt)) {
                    // ログ出力メッセージ
                    $error_msg = "在庫検索SQLプリペアエラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            }

            // SQL実行
            $result = $stmt->execute(array($_SESSION['stock_id']));
            if (PEAR::isError($result)) {
                    // ログ出力メッセージ
                    $error_msg = "在庫検索SQL実行エラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid'], $cond_day), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            }
            // フェッチ
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            if (is_null($row)) {
                    // ログ出力メッセージ
                    $error_msg = "該当する在庫がありません。";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid'], $cond_day), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            } else {
                    if ($row['poke_wait_headcount'] > 0) {
                            return BOOK_OUT_OF_STOCK;
                    }
            }
            $stmt->free();

            // 在庫数の確認(在庫数－既申込数)
            // SQL文
            $sql = "SELECT sum(adult_headcount) as adult_headcount,sum(child_headcount) as child_headcount ";
            $sql .= " ,sum(adult_wait_headcount) as adult_wait_headcount,sum(child_wait_headcount) as child_wait_headcount ";
            $sql .= " FROM ".APPDB.". bookings ";
 	    $sql .= " WHERE stock_box_id = 1 and stock_id = ?";

            // プリペア
            $stmt = $mdbapp->prepare($sql, array('text'));
            if (PEAR::isError($stmt)) {
                    // ログ出力メッセージ
                    $error_msg = "既申込数検索SQLプリペアエラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            }
            // SQL実行
            $result = $stmt->execute(array($_SESSION['stock_id']));
            if (PEAR::isError($result)) {
                    // ログ出力メッセージ
                    $error_msg = "既申込数SQL実行エラー";
                    // ログ出力
                    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid'], $cond_day), __FILE__, __LINE__);
                    return BOOK_FAILURE;
            }
            // フェッチ
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            $adult_headcount = $row['adult_headcount'];
            $child_headcount = $row['child_headcount'];
            $adult_wait_headcount = $row['adult_wait_headcount'];
            $child_wait_headcount = $row['child_wait_headcount'];

            // 在庫データ－既申込者数(キャンセル待ちの人数含む)
            $zaikocount = $max_headcount - ($adult_headcount + $child_headcount + $adult_wait_headcount + $child_wait_headcount);
            //同一セッション内申込者数
            $totalnum = $_SESSION['mannum'] + $_SESSION['womannum'] + $_SESSION['boynum'] + $_SESSION['girlnum'];

            // 在庫データ－既申込者数 >= 同一セッション内申込者数のとき申込処理を続行する
            if ($totalnum > $zaikocount) {
                    $stmt->free();
                    return BOOK_OUT_OF_STOCK;
            }
            $stmt->free();
        }

    // トランザクション開始
    $result = $mdbwww->beginTransaction();
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "トランザクション開始エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
        return BOOK_FAILURE;
    }

    // 予約番号発行
    // SQL実行
    $result = $mdbwww->query('SELECT lastnumber FROM d_bookingnumber WHERE numberid = 1 FOR UPDATE');
	if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "予約番号検索SQL実行エラー";
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
        return BOOK_FAILURE;
    }

    // フェッチ
    $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
    if (is_null($row)) {
        // ログ出力メッセージ
        $error_msg = "予約番号検索SQLフェッチエラー";
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
        // 予約処理異常返却
        return BOOK_FAILURE;
    }

    // 新予約番号
    $newNumber = 1 + $row['lastnumber'];

    // SQL文
    $sql = "UPDATE d_bookingnumber SET lastnumber = $newNumber ";
    $sql .= "WHERE numberid = 1";

    // SQL実行(予約番号更新)
    $result = $mdbwww->query($sql);
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "予約番号更新SQL実行エラー";
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
        return BOOK_FAILURE;
    }

    $_SESSION['webbookingnumber'] = substr(str_repeat('0', 8).$newNumber, -8, 8);

    // 予約登録
    // SQL文
    $sql = 'INSERT INTO d_booking ';
    $sql .= '(bookingnumber';
    $sql .= ',mailaddress';
    $sql .= ',checkkey';
    $sql .= ',eventid';
    $sql .= ',saikouday';
    $sql .= ',thirdid';
    $sql .= ',mannum';
    $sql .= ',womannum';
    $sql .= ',boynum';
    $sql .= ',girlnum';
    $sql .= ',sel1_name';
    $sql .= ',sel1_headcount';
    $sql .= ',sel2_name';
    $sql .= ',sel2_headcount';
    $sql .= ',sel3_name';
    $sql .= ',sel3_headcount';
    $sql .= ',sel4_name';
    $sql .= ',sel4_headcount';
    $sql .= ',sel5_name';
    $sql .= ',sel5_headcount';
    $sql .= ',adultfee';
    $sql .= ',childfee';
    $sql .= ',lastnamekanji';
    $sql .= ',firstnamekanji';
    $sql .= ',lastnamekana';
    $sql .= ',firstnamekana';
    $sql .= ',postalcode';
    $sql .= ',address1';
    $sql .= ',address2';
    $sql .= ',address3';
    $sql .= ',telephone';
    $sql .= ',merumagayukouflg';
    $sql .= ',bookingnumberhakouday';
    $sql .= ',duemethod';
    $sql .= ',tosalesforce';
    $sql .= ',tosalesforcedate';
    $sql .= ',tosalesforcenum';
    $sql .= ',fromsalesforce';
    $sql .= ',fromsalesforcedate';
    $sql .= ',tocustmermail';
    $sql .= ',tocustmermaildate';
    $sql .= ',uketukestatus';
    $sql .= ',insdate';
    $sql .= ',currentatid';
    $sql .= ',currentasid';
    $sql .= ',currentasmgtid';
    $sql .= ',firstatid';
    $sql .= ',firstasid';
    $sql .= ',firstvisitday';
    $sql .= ',salesforcenolinkflg';
    $sql .= ',topokemail';
    $sql .= ',topokemaildate';
    $sql .= ',lang';
    $sql .= ',country';
	$sql .= ',member_id';
    $sql .= ') VALUES ';
    $sql .= '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
    $sql .= ', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
    $sql .= ', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
    $sql .= ', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
    $sql .= ', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $datatype = array('text'   , 'text'   , 'text'   , 'text'   ,
                      'text'   , 'text', 'integer'   ,'integer' , 'integer',
                      'integer', 'text'   , 'integer','text'    , 'integer',
		      'text'   , 'integer','text'    , 'integer', 'text'   ,
		      'integer', 'integer', 'integer', 'text'   ,
                      'text'   , 'text'   , 'text'   , 'text'   ,
                      'text'   , 'text'   , 'text'   , 'text'   ,
                      'integer', 'text'   , 'integer', 'integer', 'text'   ,
                      'integer', 'integer', 'text'   , 'integer',
                      'text'   , 'text'   , 'text'   , 'text'   , 'text'   ,
                      'text'   , 'text'   , 'text'   , 'text'   ,
                      'integer', 'integer', 'text', 'text', 'text', 'integer');
    // プリペア
    $stmt = $mdbwww->prepare($sql, $datatype, MDB2_PREPARE_MANIP);
    if (PEAR::isError($stmt)) {
        // ログ出力メッセージ
        $error_msg = "予約データ登録SQLプリペアエラー";
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
        return BOOK_FAILURE;
    }

    // 現在時刻取得(予約申込完了日付,作成日付に使用)
    $bookingdt = time();

    // 初回訪問日フォーマット変更
    $fv_day = strval(intval($_SESSION['firstvisitday']));
    if ($fv_day == $_SESSION['firstvisitday']) {
        if (strlen($fv_day) == 8) {
            $temp2_day = mktime(0, 0, 0, substr($fv_day, 4, 2), substr($fv_day, 6, 2), substr($fv_day, 0, 4));
            if ($temp2_day == FALSE) {
                $error_msg = '初回訪問日のフォーマットが不正です。';
                logOnline($error_msg, ONLINE_LOG_LEVEL_WARN, array($_SESSION['firstvisitday']), __FILE__, __LINE__);
                $firstvisit_day = '';
            } else {
                $firstvisit_day = date('Y-m-d', $temp2_day);
            }
        } else {
            $firstvisit_day = '';
        }
    } else {
        $error_msg = '初回訪問日のフォーマットが不正です。';
        logOnline($error_msg, ONLINE_LOG_LEVEL_WARN, array($_SESSION['firstvisitday']), __FILE__, __LINE__);
        $firstvisit_day = '';
    }
    
    // Data
    $data = array(substr(str_repeat('0', 8).$newNumber, -8, 8)
                 ,$_SESSION['mailaddress']
                 ,$_SESSION['checkkey']
                 ,$_SESSION['eventid']
                 ,$cond_day
                 ,$_SESSION['thirdid']
                 ,$_SESSION['mannum']
                 ,$_SESSION['womannum']
                 ,$_SESSION['boynum']
                 ,$_SESSION['girlnum']
                 ,$_SESSION['optname1']
                 ,$_SESSION['optnum1']
                 ,$_SESSION['optname2']
                 ,$_SESSION['optnum2']
                 ,$_SESSION['optname3']
                 ,$_SESSION['optnum3']
                 ,$_SESSION['optname4']
                 ,$_SESSION['optnum4']
                 ,$_SESSION['optname5']
                 ,$_SESSION['optnum5']
                 ,$_SESSION['adultfee']
                 ,$_SESSION['childfee']
                 ,$_SESSION['lastnamekanji']
                 ,$_SESSION['firstnamekanji']
                 ,$_SESSION['lastnamekana']
                 ,$_SESSION['firstnamekana']
                 ,$_SESSION['postalcode1'].$_SESSION['postalcode2']
                 ,$_SESSION['address1']
                 ,$_SESSION['address2']
                 ,$_SESSION['address3']
                 ,$_SESSION['telephone1'].'-'.$_SESSION['telephone2'].'-'.$_SESSION['telephone3']
                 ,($_SESSION['mailmag'] == 'ok' ? MAIL_MAGAZINE_ON : MAIL_MAGAZINE_OFF)
                 ,date('Y-m-d H:i:s', $bookingdt)
				 ,$_SESSION['duemethod']
                 ,TO_SALESFORCE_NOT_COMPLETE
                 ,NULL
                 ,0
                 ,FROM_SALESFORCE_NOT_COMPLETE
                 ,NULL
                 ,TO_CUSTOMER_MAIL_NOT_COMPLETE
                 ,NULL
                 ,$_SESSION['uketukestatus']
                 ,date('Y-m-d', $bookingdt)
                 ,$_SESSION['currentatid']
                 ,$_SESSION['currentasid']
                 ,$_SESSION['currentasmgtid']
                 ,$_SESSION['firstatid']
                 ,$_SESSION['firstasid']
                 ,$firstvisit_day
                 ,($_SESSION['salesforcenolinkflg'] == SF_NOLINK_FLAG_NOLINK ? SF_NOLINK_FLAG_NOLINK : SF_NOLINK_FLAG_LINK)
                 ,TO_SFNOLINKMAIL_NOT_COMPLETE
                 ,NULL
                 ,RTS_LANG_JAPANESE
                 ,'Japan'
				 ,$_SESSION['member_id']
        );
    // SQL実行
    $result = $stmt->execute($data);
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "予約データ登録SQL実行エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, $data, __FILE__, __LINE__);
        // ロールバック
		$ret = $mdbwww->rollback();
        if (PEAR::isError($ret)) {
            // ログ出力メッセージ
            $error_msg = "ロールバックエラー";
            // ログ出力
            logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
        }
        return BOOK_FAILURE;
    }

    $stmt->free();

    // メルマガテーブル登録
    $merumagayukouflg = ($_SESSION['mailmag'] == 'ok' ? MAIL_MAGAZINE_ON : MAIL_MAGAZINE_OFF);
    $ret = insertMailMagazineTable($_SESSION['mailaddress'], $merumagayukouflg);
    if ($ret != INS_MAILMAGAZINE_NORMAL) {
        // ログ出力メッセージ
        $error_msg = "メルマガテーブル登録エラー発生";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['mailaddress'], $merumagayukouflg), __FILE__, __LINE__);

        // ロールバック
		$ret = $mdbwww->rollback();
        if (PEAR::isError($ret)) {
            // ログ出力メッセージ
            $error_msg = "ロールバックエラー";
            // ログ出力
            logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, __FILE__, __LINE__);
        }
        return BOOK_FAILURE;
    }

	//ステータスの更新_APPへ送信
	$wrkdate = date("Y/m/d H:i:s");
	// SQL文
	$sql = 'UPDATE d_booking SET ';
	$sql .= ' tosalesforce = "'.TO_SALESFORCE_COMPLETE.'"';
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
		return BOOK_FAILURE;
	}

        //性別
        $gender='';
        if($_SESSION['gender'] == "男性") {
            $gender = '男';
        }elseif($_SESSION['gender'] == "女性"){
            $gender = '女';
        }
        
        //情報誌発送
        $document_flg = 0;
        $member_id = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : "";
        if($member_id !== ""){
            $document_flg = 0;
        }else if($isJCOM){
          $document_flg = $_SESSION['documentflg'] == "ng" ? 0:1;
        }
        
        //当日運営者への連絡事項
        $holding_notes = '';
        if($_SESSION['telephone1'] == '070' || $_SESSION['telephone1'] == '080' || $_SESSION['telephone1'] == '090'){
          $holding_notes = $_SESSION['telephone1']."-".$_SESSION['telephone2']."-".$_SESSION['telephone3'];
        }
        
        
        
	//予約データを配列にセット
	$bookingdata = array("name" => $_SESSION['lastnamekanji'].'　'.$_SESSION['firstnamekanji']
		,"last_name_kana" =>  $_SESSION['lastnamekana']
		,"first_name_kana" =>  $_SESSION['firstnamekana']
                ,"gender" => $gender
                ,"birth_year" => $_SESSION['birth_year']
                ,"birth_month" => $_SESSION['birth_month']
                ,"birth_day" => $_SESSION['birth_day']
		,"zip" =>  $_SESSION['postalcode1']."-".$_SESSION['postalcode2']
                ,"lang" =>  RTS_LANG_JAPANESE
                ,"country" =>  'Japan'
		,"pref" =>  $_SESSION['address1']
		,"city" =>  $_SESSION['address2']
		,"street" =>  mb_convert_kana($_SESSION['address3'], 'a', INTERNAL_ENCODE)
		,"phone" =>  $_SESSION['telephone1']."-".$_SESSION['telephone2']."-".$_SESSION['telephone3']
		,"email" =>  $_SESSION['mailaddress']
        ,"document_flg" => $document_flg
		,"merumaga_flg" => ($_SESSION['mailmag'] == 'ok' ? MAIL_MAGAZINE_ON : MAIL_MAGAZINE_OFF)
		,"from_web_flg" =>  WEB_APPLY_FLG
		,"event_id" =>  $_SESSION['eventno']
		,"stock_id" =>  $_SESSION['stock_id']
		,"stock_box_id" => 1
        ,"medianame_id" => 25
        ,"medianumber_id" => 792
        ,"asid" =>  $_SESSION['currentasid']
		,"sel1_headcount" =>  $_SESSION['optnum1']
		,"sel2_headcount" =>  $_SESSION['optnum2']
		,"sel3_headcount" =>  $_SESSION['optnum3']
		,"sel4_headcount" =>  $_SESSION['optnum4']
		,"sel5_headcount" =>  $_SESSION['optnum5']
		,"adult_headcount" =>  $_SESSION['mannum']
		,"child_headcount" =>  $_SESSION['boynum']
		,"status_id" => 7
		,"web_checkkey" =>  $_SESSION['checkkey']
		,"web_booking_number" =>  $newNumber
		,"adult_fee" =>  $_SESSION['baseadultfee']
        ,"child_fee" =>  $_SESSION['basechildfee']
		,"adult_discount_rate" =>  $_SESSION['affiliate_discount_rate']
		,"adult_discount_amount" =>  $adult_discount_rate
		,"child_discount_rate" =>  $_SESSION['affiliate_discount_rate']
		,"child_discount_amount" =>  $child_discount_rate
		,"postage" => $_SESSION['postage']
		,"account_method_type" => $account_method_type
		,"dsk_count" => $dsk_count
		,"gbk_count" => $gbk_count
        ,"covenant_count" => $covenant_count
            ,"holding_notes" => $holding_notes
        ,"contact_user_id" => 44
        ,"input_user_id" => 44
            ,"first_contact_dt" => date('Y-m-d', $bookingdt)
		,"origin_type" => 6
		,"incident_type_id" =>  2
		,"member_id" => $_SESSION['member_id']);


    // ベーシック認証→予約データをPostで送信
    $res = basiccertify($bookingdata);
	//受け取った戻り値を仕分け
	$res = preg_split('/[{},:]/', $res);
	//実行結果の判定
	switch ($res[2]) {
		//予約申込み完了
		case BOOK_COMPLETE:
			if ($res[4] != "") {
				//予約番号をセット
				$_SESSION['bookingnumber'] = str_replace("\"","",$res[4]);
				break;
			} else {
				// ログ出力メッセージ
            	$error_msg = "APPシステムデータ登録エラー1";
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
            	return BOOK_FAILURE;
            	break;
			}
		//予約申込み処理異常
		case BOOK_FAILURE:
			// ログ出力メッセージ
        	$error_msg = "APPシステムデータ登録エラー2";
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
			return BOOK_FAILURE;
			break;
		//予約申込み在庫なし
		case BOOK_OUT_OF_STOCK:
			// ログ出力メッセージ
			$error_msg = "APPシステム在庫切れエラー";
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
			//在庫切れページへ遷移
			return BOOK_OUT_OF_STOCK;
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
			return BOOK_FAILURE;
			break;
	}

	//ステータスの更新_APPから受信
	$wrkdate = date("Y/m/d H:i:s");
	// SQL文
	$sql = 'UPDATE d_booking SET ';
    $sql .= ' poke_app_bookingnumber = "'.$_SESSION['bookingnumber'].'"';
	$sql .= ' ,fromsalesforce = "'.FROM_SALESFORCE_COMPLETE.'"';
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
		return BOOK_FAILURE;
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
        return BOOK_FAILURE;
    }
    return BOOK_COMPLETE;
}

//オーソリ実行
function doauth() {

	global $mdbwww;
	global $mdbapp;

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
	$totalfee = $_SESSION['adultfee'] + $_SESSION['childfee'] + $_SESSION['postage'];

	//オーソリ実施
	tryauth($_SESSION['bookingnumber'],$totalfee,'sp');
}

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
<title>イベントツアー予約 | ポケカル</title>
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


</head>
<body onLoad="f_ChangeInputColor()">
<?php include_once("tags/body_tag.php"); ?>
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
<div class="main">

<!-- ##### BODY ##### -->
	<section>
        
<!-- ##### BODY ##### -->

		<h2 class="headline01 fs">
			イベント・ツアー予約(予約内容確認)
		</h2>
		
		<ol class="cart_stepbar">
			<li><span>1</span></li>
			<li class="visited"><span>2</span></li>
			<li><span>3</span></li>
			<li><span>4</span></li>
			<li><span>5</span></li>
			<li><span>6</span></li>			
		</ol>
		
		<div class="carttableTop">
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
			<hr/>
			<div class="carttableInfo">
				<?php
				if($_SESSION['paymethod'] == 'discard' || $_SESSION['paymethod'] == 'cash') {
				?>
				<div style="text-align:left;">
					上記内容で間違いなければ、「予約を確定する」ボタンを押してください。
				</div>
				<?php
				}elseif($_SESSION['paymethod'] == 'card') {
				?>
					<div style="color:#333333;">
						上記内容で間違いなければ、「予約を確定する」ボタンを押してください。次の画面で、クレジットカードの決済手続きを進めてまいります。
					</div>
				<?php
				}else{
					
				}
				?>
				<?php 
				if($isJCOM) { 
				?>
					<div style="color:red">
						※予約受付期間終了後、当選通知メールをお届けします。
					</div>
				<?php 
				} 
				?>	
			</div>
			<div class="wrapper" onclick="return judge()">
				<a id="p1" class="btn-detail" onClick="document.frm_booking.submit();return false;">キャンセル規定に同意の上､予約を確定する</a>
			</div>
			
			<div class="carttableInfo">
				<div style="color:#F00; text-align: center;">
					※ボタンは2回以上押さないようお願いいたします。
				</div>
			</div>




		</form>


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
