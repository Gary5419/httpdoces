<?php
/**
 * 予約入力画面
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

#マイページここから
$member_id = isset($_REQUEST['member_id']) ? $_REQUEST['member_id'] : "";
$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : "";

$login_flag = false;
if($member_id != "" && $session_id != ""){
	require_once '../../class_mypage/db_extension.php';
	$dbname = "pokedb";
	$table = "members";
	$columns = "*";
	$postfix = "where session_id = '{$session_id}' and id ='{$member_id}'  ";
	$result = DbEx::select($dbname, $table, $columns, $postfix);
	if( count($result) > 0){
		$login_flag = true;
		$data = $result[0];
		$tmp = explode("-",$data['birthday']);
		$data['birthday_y'] = $tmp[0];
		$data['birthday_m'] = $tmp[1];
		$data['birthday_d'] = $tmp[2];
		
		$tmp = explode("-",$data['tel1']);
		$data['tel1_1'] = $tmp[0];
		$data['tel1_2'] = $tmp[1];
		$data['tel1_3'] = $tmp[2];
		
		$tmp = explode("-",$data['zip']);
		$data['zip_1'] = $tmp[0];
		$data['zip_2'] = $tmp[1];
		$m_pref = array( 
		1 => '北海道',2 => '青森県',3 => '岩手県',4 => '宮城県',5 => '秋田県',6 => '山形県',7 => '福島県',8 => '茨城県',9 => '栃木県',10 => '群馬県',11 => '埼玉県',12 => '千葉県',13 => '東京都',14 => '神奈川県',15 => '新潟県',16 => '富山県',17 => '石川県',18 => '福井県',19 => '山梨県',20 => '長野県',21 => '岐阜県',22 => '静岡県',23 => '愛知県',24 => '三重県',25 => '滋賀県',26 => '京都府',27 => '大阪府',28 => '兵庫県',29 => '奈良県',30 => '和歌山県',31 => '鳥取県',32 => '島根県',33 => '岡山県',34 => '広島県',35 => '山口県',36 => '徳島県',37 => '香川県',38 => '愛媛県',39 => '高知県',40 => '福岡県',41 => '佐賀県',42 => '長崎県',43 => '熊本県',44 => '大分県',45 => '宮崎県',46 => '鹿児島県',47 => '沖縄県'
	);
		$data['prefecture'] = $m_pref[$data['pref']];
	}
}
#マイページここまで

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
//確認キー画面非表示対応
switch (CHECKKEYUSEFLG) {
	//非表示
	case 0:
		$screenid_before = SCREEN_ID_CALENDER;
		$filename_before = FILENAME_CALENDER;
	break;
	//表示
	case 1:
		$screenid_before = SCREEN_ID_KEYINPUT;
		$filename_before = FILENAME_KEYINPUT;
	break;
	//異常系
	default:
		// ログ出力メッセージ
	    $error_msg = "確認キー画面表示フラグなし";
	    // ログ出力
	    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL);
	    // システムエラー画面にリダイレクト
	    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	    header("HTTP/1.0 301 Moved Permanently");
	    header($locate);
	    exit();
}

// 自画面
$screenid_current = SCREEN_ID_BOOKINPUT;
$filename_current = FILENAME_BOOKINPUT;

// 次画面
$screenid_next = SCREEN_ID_PREVIEW;
$filename_next = FILENAME_PREVIEW;

$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

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
	//確認キー画面非表示対応
	if (CHECKKEYUSEFLG == 0) {
		// 予約系最初の画面なので無条件に前画面からの遷移とみなす
		$past_screen_ind = PAST_SCREEN_IND_BEFORE;
	}else{
	    // 前画面チェックOK遷移判定
	    if ($_SESSION['screenid'] == $screenid_before) {
	        // 前画面チェックOK遷移
	        $past_screen_ind = PAST_SCREEN_IND_BEFORE;
	    } else {
	        // 前画面チェックOK遷移以外
	        $past_screen_ind = PAST_SCREEN_IND_OTHER;
	    }
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

        // 自画面からのデータのフィルタリング
        filterPostData();

        // 自画面からのデータの入力チェック
        checkPostData($user_error);

        // チェック判定
        if (cmIsEmpty($user_error)) {    // チェックOK
            // 入力データを格納
            storePostData();
	    //確認キー画面非表示対応
            if (CHECKKEYUSEFLG == 1) {
           	 // 各処理
            	// 確認キー有効チェック(入力チェックOKの場合のみ行う)
            	$res = validateCheckKey($_SESSION['mailaddress'], $_SESSION['checkkey'], $error);

            	if ($res == VALIDATE_CHECKKEY_VALID) {          // 確認キー正常
                	// 次画面にリダイレクト
                	$locate = "Location: " .$url_currentdir.$filename_next;
                	header("HTTP/1.0 301 Moved Permanently");
                	header($locate);
                	exit();
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
	        } else {     // 確認キーチェック処理エラー
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
	    } else {
			//確認キー画面非表示対応
			if (CHECKKEYUSEFLG == 0) {
	            // 確認キー発行
	            $ret = publishCheckKey($_SESSION['mailaddress'], $checkkey, $md5key, $system_error);
	            if (!$ret) {    // 確認キー発行処理異常
	                // システムエラー画面にリダイレクト
	                $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	                header("HTTP/1.0 301 Moved Permanently");
	                header($locate);
	                exit();
		    	}
			    //確認キーをセット
			    $_SESSION['checkkey'] = $checkkey;
            }
			// 次画面にリダイレクト
                	$locate = "Location: " .$url_currentdir.$filename_next;
                	header("HTTP/1.0 301 Moved Permanently");
                	header($locate);
                	exit();
			}
        } else {                    // チェックNG
            // 表示のみの制御なので、ここでは何もしない
        }

    break;

    case PAST_SCREEN_IND_BEFORE:
        // 遷移元が前画面の場合

        // 操作開始時刻保存
        setOperationStartTime();

        // 引継ぎデータ以外をクリア
        clearData();

		//確認キー非表示対応
		if (CHECKKEYUSEFLG == 0) {
		    // 予約申込みフラグを初期化
			$_SESSION['resbook'] = BOOK_INITIALIZED;

		    // 予約系最初の画面なのでPOSTで引き継いだデータを格納
			$_SESSION['eventid'] = mb_convert_kana($_POST['eventid'],"a","utf8");
		    $_SESSION['saikouday'] = $_POST['saikouday'];
			$_SESSION['eventname'] = $_POST['eventname'];
		    $_SESSION['cancelkiteikbn'] = $_POST['cancelkiteikbn'];
			$_SESSION['salesforcenolinkflg'] = $_POST['salesforcenolinkflg'];
			$_SESSION['thirdid'] = $_POST['thirdid'];
		    $_SESSION['stock_id'] = $_POST['stock_id'];
                    $_SESSION['auto_furiwake'] = $_POST['auto_furiwake'];
                    $_SESSION['lang'] = $_POST['lang'];

			// アフィリエイトパラメータ格納
			storeAffiliateParam();
		}
    break;

    default:
        // 遷移元がその他の場合

        // 不正操作エラー画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
        exit();

    break;

}

/*
 * Cookie情報取得
 */
//カレントasid
if(isset($_COOKIE["pokecurrent"]) and !cmIsEmpty($_COOKIE["pokecurrent"])){
    // cookieが存在する場合
    $cookiedata = getCook("pokecurrent");
    $_SESSION['currentatid'] = $cookiedata[0];
    $_SESSION['currentasid'] = $cookiedata[1];
    $currentvisitdatetime = $cookiedata[2];
    $_SESSION['currentasmgtid'] = $cookiedata[3];
    $systemdatetime = date('Y/m/d H:i:s');
    $visittime=strtotime($currentvisitdatetime);
    $systemtime = strtotime($systemdatetime);
    $diff = $systemtime - $visittime;
    //1時間以上経っている場合は無効
    if($diff > 3600){
	$_SESSION['currentatid'] = '0';
	$_SESSION['currentasid'] = '0';
	$_SESSION['currentvisitdate'] = '';
        $_SESSION['currentasmgtid'] = '';
	// cookie削除
	setcookie('pokecurrent', '', time() - 3600);
    }
}else{
    $_SESSION['currentatid'] = '0';
    $_SESSION['currentasid'] = '0';
    $_SESSION['currentvisitdate'] = '';
    $_SESSION['currentasmgtid'] = '';
}
//初回訪問日
if(isset($_COOKIE["pokekaru"]) and !cmIsEmpty($_COOKIE["pokekaru"])){
    // cookieが存在する場合
    $cookiedata_first = getCook("pokekaru");
    $_SESSION['firstatid'] = $cookiedata_first[0];
    $_SESSION['firstasid'] = $cookiedata_first[1];
    $_SESSION['firstvisitday'] = $cookiedata_first[2];
}else{
    $_SESSION['firstatid'] = '';
    $_SESSION['firstasid'] = '';
    $_SESSION['firstvisitday'] = '';
}

//特定のASID
$isJCOM = false;
if($_SESSION['currentasid'] == ASID_JCOM) {
  $isJCOM = true;
}

/**
 * 画面表示用DBアクセス
 */
// DB接続
$mdb2 =& MDB2:: factory(DB_DSN_APP);
if(PEAR::isError($mdb2)) {
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





/*20110621.sijam.kusumoto.add
 有効期限切れチェック
 SQL文
*/
// 在庫マスタ
// SQL文
$sql = "SELECT receivable_date as recdate FROM ".APPDB.".stocks sto ";
$sql .= " WHERE id = ? ";
// プリペア
$stmt = $mdb2->prepare($sql, array('text'));
if (PEAR::isError($stmt)) {
    // ログ出力メッセージ
    $error_msg = "在庫マスタ検索SQLプリペアエラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// SQL実行
$result = $stmt->execute(array($_SESSION['stock_id']));

if (PEAR::isError($result)) {
    // ログ出力メッセージ
    $error_msg = "在庫マスタ検索SQL実行エラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['stock_id']), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// フェッチ
$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
if (is_null($row)) {
    // ログ出力メッセージ
    $error_msg = "該当するイベントが在庫マスタに存在しない";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['stock_id']), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
} else {
#    if ($row['recdate'] < $row['curdate']){
    if(strtotime($row['recdate']) < strtotime("+".RECEIVESTANDARD."day")) {
        // ログ出力メッセージ
        $error_msg = "該当するイベントが在庫マスタに存在しない";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['stock_id']), __FILE__, __LINE__);

        // システムエラー画面にリダイレクト
        $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
        exit();
        
    }
}
$stmt->free();

#printf($_SESSION['stock_id'] . "," . $row['recdate'] . "," . $row['curdate']);



// asidから割引率を取得→有効でなければ割引率を0にする
// SQL文
$sql = 'SELECT available,affiliate_discount_rate ';
$sql .= 'FROM '.APPDB. '.partners WHERE asid = ? ';
// プリペア
$stmt = $mdb2->prepare($sql, array('text'));
if (PEAR::isError($stmt)) {
    // ログ出力用情報
    $system_error[] = $mdb2->getUserInfo();
    // ログ出力
    
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// SQL実行
$result = $stmt->execute(array($_SESSION['currentasid']));
if (PEAR::isError($result)) {
    // ログ出力用情報
    $system_error[] = $mdb2->getUserInfo();
    // ログ出力
    
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// フェッチ
$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
//取得結果がNULL?
if (is_null($row)) {
	//割引率を0にセット
	$affiliate_discount_rate = "0";
} else {
	//割引有効?
	if ($row['available'] != 1) {
		//割引率を0にセット
		$affiliate_discount_rate = "0";
	} else {
		//割引率をセット
	    $affiliate_discount_rate = $row['affiliate_discount_rate'];
	}
}
$_SESSION['affiliate_discount_rate'] = $affiliate_discount_rate;
$stmt->free();

// イベント名,キャンセル規定区分はSESSIONパラメータから取得
//     画面出力の際はサニタイズを忘れないこと。
$eventname      = $_SESSION['eventname'];
$cancelkiteikbn = $_SESSION['cancelkiteikbn'];
$thirdid   = $_SESSION['thirdid'];

// SF非連動フラグ
$sfnolinkflg    = $_SESSION['salesforcenolinkflg'];

// 在庫マスタ
// SQL文
$sql = "SELECT adult_fee as adultfee, child_fee as childfee,judgement as uketukestatus FROM ".APPDB.".stocks sto ";
$sql .= " WHERE id = ? ";
// プリペア
$stmt = $mdb2->prepare($sql, array('text'));
if (PEAR::isError($stmt)) {
    // ログ出力メッセージ
    $error_msg = "在庫マスタ検索SQLプリペアエラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);

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

// SQL実行
$result = $stmt->execute(array($_SESSION['stock_id']));

if (PEAR::isError($result)) {
    // ログ出力メッセージ
    $error_msg = "在庫マスタ検索SQL実行エラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['stock_id']), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// フェッチ
$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
if (is_null($row)) {
    // ログ出力メッセージ
    $error_msg = "該当するイベントが在庫マスタに存在しない";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['stock_id']), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
} else {
    $_SESSION['uketukestatus'] = $row['uketukestatus'];
	//割引率があるか?
	if ($affiliate_discount_rate == 0) {
		$_SESSION['adultfee'] = $row['adultfee'];
		$_SESSION['childfee'] = $row['childfee'];
		$_SESSION['baseadultfee'] = $row['adultfee'];
		$_SESSION['basechildfee'] = $row['childfee'];
	} else {
		$_SESSION['adultfee'] = floor($row['adultfee'] * ((100 - $affiliate_discount_rate)/100));
		$_SESSION['childfee'] = floor($row['childfee'] * ((100 - $affiliate_discount_rate)/100));
	    $_SESSION['baseadultfee'] = $row['adultfee'];
	    $_SESSION['basechildfee'] = $row['childfee'];
	}
}
$stmt->free();

/*20100622.sijam.nogu.add
 オプション取得
 SQL文
*/
//チケット対応追加 + 支払方法限定機能追加
$sql = "SELECT id as eventno,postage,sel1_name, sel2_name, sel3_name, sel4_name, sel5_name, customer_payee, web_payment_type ";
$sql .= "FROM ".APPDB.".events ";
$sql .= "WHERE eventcode = ?";
// プリペア
$stmt = $mdb2->prepare($sql, array('text'));
if (PEAR::isError($stmt)) {
    // ログ出力メッセージ
    $error_msg = "イベントマスタ検索SQLプリペアエラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// SQL実行
$result = $stmt->execute(array($_SESSION['eventid']));
if (PEAR::isError($result)) {
    // ログ出力メッセージ
    $error_msg = "イベントマスタ検索SQL実行エラー";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid']), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

// フェッチ
$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
if (is_null($row)) {
    // ログ出力メッセージ
    $error_msg = "該当するイベントがイベントマスタに存在しない";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($_SESSION['eventid']), __FILE__, __LINE__);

    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
} else {
    $_SESSION['eventno'] = $row['eventno'];
    $_SESSION['postage'] = $row['postage'];
    $_SESSION['optname1'] = $row['sel1_name'];
    $_SESSION['optname2'] = $row['sel2_name'];
    $_SESSION['optname3'] = $row['sel3_name'];
    $_SESSION['optname4'] = $row['sel4_name'];
    $_SESSION['optname5'] = $row['sel5_name'];
    $_SESSION['customer_payee'] = $row['customer_payee']; // 顧客の支払先
    $_SESSION['web_payment_type'] = $row['web_payment_type'] == "" ? "0" : $row['web_payment_type']; // 支払方法限定
}
$stmt->free();


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

$mdb2->disconnect();

/**
 * データ入力チェック
 */
function checkPostData(&$user_error) {

    // 人数チェックフラグ
    $numcheckflg = TRUE;

    // 大人人数/mannum/必須/2
    if (isParamCheckOK($user_error, $_POST['mannum'], '大人人数', true, 2, 1, '', '^[0-9]+$')) {
        // パラメータチェックOKの場合のみ最大値チェックを行う
        if ($_POST['mannum'] > MAX_MANNUM) {
            $user_error[] = '大人人数は'.MAX_MANNUM.'までです。';
            $numcheckflg = FALSE;
        }
    } else {
        $numcheckflg = FALSE;
    }
    
    // 子供人数(男)/boynum/必須/2
    if (isParamCheckOK($user_error, $_POST['boynum'], '子供人数', true, 2, 1, '', '^[0-9]+$')) {
        // パラメータチェックOKの場合のみ最大値チェックを行う
        if ($_POST['boynum'] > MAX_BOYNUM) {
            $user_error[] = '子供人数は'.MAX_BOYNUM.'までです。';
            $numcheckflg = FALSE;
        }
    } else {
        $numcheckflg = FALSE;
    }

    // 合計人数チェック
    if ($numcheckflg == TRUE) {
	$totalnumber = $_POST['mannum'] + $_POST['boynum'];
        if ($totalnumber == 0) {
            $user_error[] = '合計参加人数が0人です。';
            $numcheckflg = FALSE;
        }
    }
    
    // 合計人数とオプションの人数が等しいかチェック
	if (!is_null($_SESSION['optname1'])){
		if ($numcheckflg == TRUE) {
	       $optnumtotal =  $_POST['optnum1'] + $_POST['optnum2'] + $_POST['optnum3'] + $_POST['optnum4'] + $_POST['optnum5'];
	        if ($optnumtotal !== $totalnumber) {
	            $user_error[] = '参加人数と選択肢の合計人数が違います。';
	            $numcheckflg = FALSE;
	        }
	    }
	}

    // 姓(漢字)/lastnamekanji/必須/20
    isParamCheckOK($user_error, $_POST['lastnamekanji'], '氏名(漢字)', true, 20, 1, '', '^[一-龠ぁ-んァ-ヶー々]+$');

    // 名(漢字)/firstnamekanji/必須/20
    isParamCheckOK($user_error, $_POST['firstnamekanji'], '氏名(漢字)', true, 20, 1, '', '^[一-龠ぁ-んァ-ヶー々]+$');

    // 姓(カナ)/lastnamekana/必須/20
    isParamCheckOK($user_error, $_POST['lastnamekana'], '氏名(カナ)', true, 20, 1, '', '^[ァ-ヶー]+$');

    // 名(カナ)/firstnamekana/必須/20
    isParamCheckOK($user_error, $_POST['firstnamekana'], '氏名(カナ)', true, 20, 1, '', '^[ァ-ヶー]+$');

    // 生年月日/必須/日付型/区切り文字(-)
    //$date = $_POST['birth_year']."-".$_POST['birth_month']."-".$_POST['birth_day'];
    //isParamCheckOK($user_error, $date, '生年月日', true, 0, 0, '', '',true,'-');
    
    // 郵便番号1/postalcode1/必須/3
    isParamCheckOK($user_error, $_POST['postalcode1'], '郵便番号1', true, 3, 3, '', '^[0-9]+$');

    // 郵便番号2/postalcode2/必須/4
    isParamCheckOK($user_error, $_POST['postalcode2'], '郵便番号2', true, 4, 4, '', '^[0-9]+$');

    // 都道府県/address1/必須/4
    isParamCheckOK($user_error, $_POST['address1'], '都道府県', true, 4, 1, '', '^[一-龠]+$');

    // 市区/address2/必須/50
    isParamCheckOK($user_error, $_POST['address2'], '市区', true, 50, 1, '', '^[一-龠ぁ-んァ-ヶ々][一-龠ぁ-んァ-ヶー－\-0-9０-９ 　・々]*$');

    // 町村番地アパート・マンション/address3/必須/50
    isParamCheckOK($user_error, $_POST['address3'], '町村・番地 アパート・マンション名', true, 50, 1, '', '^[一-龠ぁ-んァ-ヶA-Za-zＡ-Ｚａ-ｚ0-9０-９‐ーｰ－―][一-龠ぁ-んァ-ヶA-Za-zＡ-Ｚａ-ｚー－\-0-9０-９‐ーｰ－― 　・々]*$');

    // 電話番号1/telephone1/必須/4
    isParamCheckOK($user_error, $_POST['telephone1'], '電話番号1', true, 4, 1, '', '^[0-9]+$');

    // 電話番号2/telephone2/必須/4
    isParamCheckOK($user_error, $_POST['telephone2'], '電話番号2', true, 4, 1, '', '^[0-9]+$');

    // 電話番号3/telephone3/必須/4
    isParamCheckOK($user_error, $_POST['telephone3'], '電話番号3', true, 4, 1, '', '^[0-9]+$');

    if (CHECKKEYUSEFLG == 0) {
	// メールアドレス/mailaddress/必須/100
	isParamCheckOK($user_error, $_POST['mailaddress'], 'メールアドレス', true, 100, 1, '', '^[0-9a-zA-Z][0-9a-zA-Z_.-]*@[0-9a-z][0-9a-z_.-]+\.[a-z]+$');
	// 確認用メールアドレス/mailaddress_confirm/必須/100
	//isParamCheckOK($user_error, $_POST['mailaddress_confirm'], '確認用メールアドレス', true, 100, 1, '', '^[0-9a-zA-Z][0-9a-zA-Z_.-]*@[0-9a-z][0-9a-z_.-]+\.[a-z]+$');
	// メールアドレス,確認用メールアドレス一致チェック
	//if ($_POST['mailaddress'] != $_POST['mailaddress_confirm']) {
	//	$user_error[] = 'メールアドレスと確認用メールアドレスが一致していません';
	//}
    }

    // メールマガジン購読
    if (($_POST['mailmag'] != 'ok') && ($_POST['mailmag'] != 'ng')) {
        $user_error[] = 'メールマガジン購読の有無を選択して下さい。';
    }

    // 支払方法がカードかそれ以外か、現地支払か選択
    if (($_POST['paymethod'] != 'card') && ($_POST['paymethod'] != 'discard') && ($_POST['paymethod'] != 'cash')) {
        $user_error[] = '料金の支払方法を選択して下さい。';
    }
}

/**
 * データ格納
 */
function storePostData() {
    $_SESSION['mannum'] = $_POST['mannum'];
    $_SESSION['womannum'] = $_POST['womannum'];
    $_SESSION['boynum'] = $_POST['boynum'];
    $_SESSION['girlnum'] = $_POST['girlnum'];
    $_SESSION['optnum1'] = $_POST['optnum1'];
    $_SESSION['optnum2'] = $_POST['optnum2'];
    $_SESSION['optnum3'] = $_POST['optnum3'];
    $_SESSION['optnum4'] = $_POST['optnum4'];
    $_SESSION['optnum5'] = $_POST['optnum5'];
    $_SESSION['lastnamekanji'] = $_POST['lastnamekanji'];
    $_SESSION['firstnamekanji'] = $_POST['firstnamekanji'];
    $_SESSION['lastnamekana'] = $_POST['lastnamekana'];
    $_SESSION['firstnamekana'] = $_POST['firstnamekana'];
    $_SESSION['postalcode1'] = $_POST['postalcode1'];
    $_SESSION['postalcode2'] = $_POST['postalcode2'];
    $_SESSION['address1'] = $_POST['address1'];
    $_SESSION['address2'] = $_POST['address2'];
    $_SESSION['address3'] = $_POST['address3'];
    $_SESSION['telephone1'] = $_POST['telephone1'];
    $_SESSION['telephone2'] = $_POST['telephone2'];
    $_SESSION['telephone3'] = $_POST['telephone3'];
    $_SESSION['personalinfo'] = isset($_POST['personalinfo']) ? $_POST['personalinfo'] : '';
    $_SESSION['mailmag'] = $_POST['mailmag'];
    $_SESSION['documentflg'] = isset($_POST['documentflg']) ? $_POST['documentflg'] : '';
    $_SESSION['mailaddress'] = $_POST['mailaddress'];
    $_SESSION['mailaddress_confirm'] = $_POST['mailaddress_confirm'];
    $_SESSION['paymethod'] = $_POST['paymethod'];
    $_SESSION['gender'] = $_POST['gender'];
    $_SESSION['birth_year'] = $_POST['birth_year'];
    $_SESSION['birth_month'] = $_POST['birth_month'];
    $_SESSION['birth_day'] = $_POST['birth_day'];
	
	$_SESSION['member_id'] = $_POST['member_id'];
}

/**
 * データクリア
 */
function clearData() {
    $_SESSION['mannum'] = '';
    $_SESSION['womannum'] = '';
    $_SESSION['boynum'] = '';
    $_SESSION['girlnum'] = '';
    $_SESSION['optname1'] = '';
    $_SESSION['optnum1'] = '';
    $_SESSION['optname2'] = '';
    $_SESSION['optnum2'] = '';
    $_SESSION['optname3'] = '';
    $_SESSION['optnum3'] = '';
    $_SESSION['optname4'] = '';
    $_SESSION['optnum4'] = '';
    $_SESSION['optname5'] = '';
    $_SESSION['optnum5'] = '';
    $_SESSION['lastnamekanji'] = '';
    $_SESSION['firstnamekanji'] = '';
    $_SESSION['lastnamekana'] = '';
    $_SESSION['firstnamekana'] = '';
    $_SESSION['postalcode1'] = '';
    $_SESSION['postalcode2'] = '';
    $_SESSION['address1'] = '';
    $_SESSION['address2'] = '';
    $_SESSION['address3'] = '';
    $_SESSION['telephone1'] = '';
    $_SESSION['telephone2'] = '';
    $_SESSION['telephone3'] = '';
    $_SESSION['personalinfo'] = '';
    $_SESSION['mailmag'] = '';
    $_SESSION['documentflg'] = '';
    $_SESSION['mailaddress'] = '';
    $_SESSION['mailaddress_confirm'] = '';
    $_SESSION['checkkey'] = '';
    $_SESSION['paymethod'] = '';
    $_SESSION['gender'] = '';
    $_SESSION['birth_year'] = '';
    $_SESSION['birth_month'] = '';
    $_SESSION['birth_day'] = '';
    $_SESSION['customer_payee'] = '';
    $_SESSION['web_payment_type'] = '';
}

/**
 * POSTパラメータフィルタリング処理
 */
function filterPostData() {
    filterControlChar($_POST['mannum']);
    filterControlChar($_POST['womannum']);
    filterControlChar($_POST['boynum']);
    filterControlChar($_POST['girlnum']);
    filterControlChar($_POST['optname1']);
    filterControlChar($_POST['optnum1']);
    filterControlChar($_POST['optname2']);
    filterControlChar($_POST['optnum2']);
    filterControlChar($_POST['optname3']);
    filterControlChar($_POST['optnum3']);
    filterControlChar($_POST['optname4']);
    filterControlChar($_POST['optnum4']);
    filterControlChar($_POST['optname5']);
    filterControlChar($_POST['optnum5']);
    filterControlChar($_POST['lastnamekanji']);
    filterControlChar($_POST['firstnamekanji']);
    filterControlChar($_POST['lastnamekana']);
    filterControlChar($_POST['firstnamekana']);
    filterControlChar($_POST['postalcode1']);
    filterControlChar($_POST['postalcode2']);
    filterControlChar($_POST['address1']);
    filterControlChar($_POST['address2']);
    filterControlChar($_POST['address3']);
    filterControlChar($_POST['telephone1']);
    filterControlChar($_POST['telephone2']);
    filterControlChar($_POST['telephone3']);
    filterControlChar($_POST['mailmag']);
    filterControlChar($_POST['mailaddress']);
    filterControlChar($_POST['mailaddress_confirm']);
    filterControlChar($_POST['checkkey']);
    filterControlChar($_POST['paymethod']);
    filterControlChar($_POST['gender']);
    filterControlChar($_POST['birth_year']);
    filterControlChar($_POST['birth_month']);
    filterControlChar($_POST['birth_day']);
}
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

<script src="/sp/common/js/script.js"></script>
<script src="/sp/common/js/formInput.js?q=151211"></script>
<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
<script src="/sp/common/js/jquery.disableDoubleSubmit.js"></script>
<script>
$(function(){
  $("form").disableDoubleSubmit(3000);
});
</script>

<script type="text/javascript">
//郵便番号→住所変換プログラム
function f_onBlur(obj){
    if("postalcode2" == obj.name){
	AjaxZip3.zip2addr('postalcode1','postalcode2','address1','address2','address3');
    }
}

//半角へのプログラム
function zenhan(obj){
 a = obj.value;
	
 //10進数の場合
 a = a.replace(/[Ａ-Ｚａ-ｚ０-９]/g, (s) => {
  return String.fromCharCode(s.charCodeAt(0) - 65248);
 });

 //16進数の場合
 a =a.replace(/[Ａ-Ｚａ-ｚ０-９]/g, (s) => {
  return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
 });
 
 //alert(id);

 //TextBoxへ変換後の文字列を戻す。
 $("#"+obj.id).val(a);
}
</script>	  
	  

<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->

</head>
<!--  <body onLoad="f_ChangeInputColor()">-->
<body>
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
			if($isJCOM) {
			?>
				<p style="color:red">※本ツアーは多数の応募が想定されるため、ツアー予約後に抽選を行い、当選した方のみご参加頂けます。</p>
				<p style="color:red">※抽選結果は、後日メールにて全申込者にご連絡いたします。</p>
			<?php
			}
			?>
			<?php
			$tgtday = $_SESSION['saikouday'];
			$trandate = date("Y/m/d H:i:s");
			$calcdays = compareDate(substr($tgtday,0,4), substr($tgtday,4,2), substr($tgtday,6,2), substr($trandate,0,4), substr($trandate,5,2),substr($trandate,8,2));
			if ($calcdays >= DUEBASEDATE  && $_SESSION['web_payment_type'] == "1") {
			?>
			<?php
			}else{
			?>
				<p>開催日が近いため、当商品のお支払いはクレジットカードのみとなります。</p>
			<?php
			}
			?>
			
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
				print("&nbsp;".htmlspecialchars($_SESSION['thirdid'], ENT_QUOTES));
				}
			?>
			</div>
			
			<div class="mb10"></div>
			<h4 class="headline07">
				申し込み人数
				<span class="require">
					必須
				</span>
			</h4>
			<div class="carttableInfo">
				<ul>
					<li class="clearfix">
						<p class="txt01">
							大人
						</p>　
						<p class="strong-red">
						<?php
							//割引が適用されているか判断
							if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
								print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['baseadultfee'].")", ENT_QUOTES));
							} else {
								print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES));
							}
						?>
							円
						</p>　
						<div class="select">
							<select name="mannum" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
							<?php
								if (array_key_exists('mannum', $_POST)) {
									$tmp_mannum = $_POST['mannum'];
								} else {
									$tmp_mannum = 0;
								}
								for($i = 0; $i < MAX_MANNUM + 1; $i++){
									if ($i == $tmp_mannum) {
										print("<option value=\"$i\" selected>$i</option>\n");
									} else {
										print("<option value=\"$i\">$i</option>\n");
									}
								}
							?>
							</select>
						</div>
						<p class="txt02">
							人
						</p>
					</li>
				</ul>
				<div class="mb10"></div>

				<?php
				// 子供料金未設定の判断
				if (!$childfee_unsetflg) {
				// 子供料金設定ありの場合
				?>
					<ul>
						<li class="clearfix">
							<p class="txt01">
							子供　
							</p>
							<p class="strong-red">
								<?php
								//割引が適用されているか判断
								if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
									print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['basechildfee'].")", ENT_QUOTES));
								} else {
									print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES));
								}
								?>
								円
							</p>
							<div class="select">
								<select name="boynum" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
								<?php
									if (array_key_exists('boynum', $_POST)) {
										$tmp_boynum = $_POST['boynum'];
									} else {
										$tmp_boynum = 0;
									}
									for($i = 0; $i < MAX_BOYNUM + 1; $i++){
										if ($i == $tmp_boynum) {
											print("<option value=\"$i\" selected>$i</option>\n");
										} else {
											print("<option value=\"$i\">$i</option>\n");
										}
									}
								?>
								</select>
							</div>
							<p class="txt02">
								人
							</p>
						</li>
					</ul>
					<div class="mb10"></div>
				<?php
				} else {
				// 子供料金設定なしの場合
				?>
					<input type="hidden" name="boynum" value=0 />
					<input type="hidden" name="girlnum" value=0 />
				<?php
				}
				
				if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
				?>
					<span>
						<div class="mb5"></div>
						お客様には、割引料金にてご提供させていただきます。
						<font Color=red><b>割引料金</b></font>(定価)
					</span>
				<?php
				}
				?>
				<?php
					//イベントカテゴリがチケットであれば送料発生
					if($_SESSION['postage'] != "" || $_SESSION['postage'] != null) {
				?>
						<span><strong>
						<div class="mb5"></div>
						※当イベントはチケット送料が
				<?php
						print($_SESSION['postage']);
				?>
						円発生します。あらかじめご了承ください。
						</strong></span>
				<?php
					}
				?>
			</div>	
			
			<?php
			// オプションがあるイベントか判断
			if (!$option_unsetflg) {
				print("<div class=\"mb10\"></div>");
				print("<h4 class=\"headline07\">選択肢	<span class=\"require\">必須</span></h4>");
				print("<div class=\"carttableInfo\">");
					// オプションありのイベントの場合
					//オプション数の上限までループ
					for($optcnt = 1; $optcnt < OPTSUBJECT + 1; $optcnt++){
		
						$optname = 'optname'.$optcnt;
						$optnum = 'optnum'.$optcnt;
						//セッション内の指定のカラムにデータがあるか
						if (!is_null($_SESSION[$optname])) {
							print("<ul>");
								print("<li class=\"clearfix\">");		
								print("<p class=\"txt03\">");
									print("$_SESSION[$optname]");
								print("</p>");
								print("<div class=\"select\">");
									print("<select name=\"$optnum\" onFocus=\"f_onFocus(this)\" onBlur=\"f_onBlur(this)\">");
										if (array_key_exists($optnum, $_POST)) {
											$tmp_optnum = $_POST[$optnum];
										} else {
										$tmp_optnum = 0;
										}
										for($i = 0; $i < MAX_OPTNUM + 1; $i++){
											if ($i == $tmp_optnum) {
												print("<option value=\"$i\" selected>$i</option>\n");
											} else {
												print("<option value=\"$i\">$i</option>\n");
											}
										}
									
									print("</select>");
								print("</div>");
								?>
								<p class="txt02">
									人
								</p>
								<?php
								print("</li>");
							print("</ul>");
							print("<div class=\"mb10\"></div>");
						}

					}
				print("</div>");
			}
			?>
	
			<div class="mb10"></div>
	
			<h3 class="headline06">
				ご連絡先		
			</h3>

			<div class="carttableInfo">
				<?php
				if($isJCOM) {
				?>
					<div style="color:red">※J:COMにご登録しているご住所・電話番号を入力してください。</div>
				<?php
				}
				?>

				<table class="tblUserInfo">
					<tbody>
						<tr>
							<td>
								氏名<br>
								<span class="require2">
									必須
								</span>							
							</td>
							<td>
								<input class="textbox txtName" type="text" name="lastnamekanji" placeholder="（例）山本" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('lastnamekanji', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['lastnamekanji'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['sei'].'" ';	
									}
								?>
								size="13" />

								<input class="textbox txtName" type="text" name="firstnamekanji" placeholder="（例）太郎" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('firstnamekanji', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['firstnamekanji'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['mei'].'" ';	
									}
								?>
								size="13" />							
							</td>
						</tr>
						<tr>
							<td>
								シメイ<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<input class="textbox txtName" type="text" name="lastnamekana" placeholder="（例）ヤマモト" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('lastnamekana', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['lastnamekana'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['sei_kana'].'" ';	
									}
								?>
								size="13" />

								<input class="textbox txtName" type="text" name="firstnamekana" placeholder="（例）タロウ" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('firstnamekana', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['firstnamekana'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['mei_kana'].'" ';	
									}
								?>
								size="13" />								
							</td>
						</tr>
						<tr>
							<td>
								性別
							</td>
							<td>
								<?php
								if (array_key_exists('gender', $_POST)) {
									$tmp_gender = $_POST['gender'];
								} else {
									$tmp_gender = "女性";
								}
								?>
								<div class="checkSex">
									<input type="radio" name="gender" value="男性" id="mailmag-man" <?php if ($tmp_gender == "男性") {print('checked="checked" ');}elseif($login_flag == true && $data['gender'] == 1){ echo 'checked="checked" '; } ?>/>
									<label for="mailmag-man">男性</label>
									
									<input type="radio" name="gender" id="mailmag-woman" value="女性" <?php if ($tmp_gender != "男性") {print('checked="checked" ');}elseif($login_flag == true && $data['gender'] == 2){ echo 'checked="checked" '; } ?>/>
									<label for="mailmag-woman">女性</label>
								</div>								
							</td>
						</tr>
						<tr>
							<td>
								生年月日
							</td>
							<td>
								<ul>
									<li class="clearfix2">
										<div class="select selectYmd">
											<select name="birth_year" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
												<?php
												$birth_years_from = 1915;
												$birth_years_to = 2017;
												$birth_years = array();
												for($i=0;$i <= ($birth_years_to - $birth_years_from);$i++){
													array_push($birth_years, $birth_years_from+$i);
												}
												if (array_key_exists('birth_year', $_POST)) {
													$tmp_birth_year = $_POST['birth_year'];
													print("<option value=\"\"></option>\n");
												} else {
													$tmp_birth_year = FALSE;
													print("<option value=\"\" selected></option>\n");
												}

												foreach ($birth_years as $val) {
													if ($val == $tmp_birth_year) {
														print("<option value=\"".$val."\" selected>".$val."</option>\n");
													} elseif($login_flag == true && $data['birthday_y'] == $val){ 
														echo '<option value="'.$val.'" selected>'.$val.'</option>'."\n"; 
													} else {
														print("<option value=\"".$val."\">".$val."</option>\n");
													}
												}
												?>
											</select>
										</div>
										<p class="txtYmd">
											年
										</p>
										<div class="select selectYmd">
											<select name="birth_month" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
											<?php
											$birth_months = array();
											for($i=0;$i < 12;$i++){
												array_push($birth_months, $i+1);
											}
											if (array_key_exists('birth_month', $_POST)) {
												$tmp_birth_month = $_POST['birth_month'];
												print("<option value=\"\"></option>\n");
											} else {
												$tmp_birth_month = FALSE;
												print("<option value=\"\" selected></option>\n");
											}

											foreach ($birth_months as $val) {
												if ($val == $tmp_birth_month) {
													print("<option value=\"".$val."\" selected>".$val."</option>\n");
												} elseif($login_flag == true && $data['birthday_m'] == $val){ 
													echo '<option value="'.$val.'" selected>'.$val.'</option>'."\n"; 
												} else {
													print("<option value=\"".$val."\">".$val."</option>\n");
												}
											}
											?>
											</select>
										</div>
										<p class="txtYmd">
											月
										</p>

										<div class="select selectYmd">
											<select name="birth_day" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
											<?php
											$birth_days = array();
											for($i=0;$i < 31;$i++){
												array_push($birth_days, $i+1);
											}
											if (array_key_exists('birth_day', $_POST)) {
												$tmp_birth_day = $_POST['birth_day'];
												print("<option value=\"\"></option>\n");
											} else {
												$tmp_birth_day = FALSE;
												print("<option value=\"\" selected></option>\n");
											}

											foreach ($birth_days as $val) {
												if ($val == $tmp_birth_day) {
													print("<option value=\"".$val."\" selected>".$val."</option>\n");
												} elseif($login_flag == true && $data['birthday_d'] == $val){ 
													echo '<option value="'.$val.'" selected>'.$val.'</option>'."\n"; 
												} else {
													print("<option value=\"".$val."\">".$val."</option>\n");
												}
											}
											?>
											</select>								
										</div>
										<p class="txtYmd">
											日
										</p>

									</li>	
								</ul>							
							</td>
						</tr>
						<tr>
							<td>
								〒郵便<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<input class="textbox zipcode1" type="text" id="postalcode1" name="postalcode1" placeholder="000" maxlength="3" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"　style="ime-mode: disabled"
								<?php
									if (array_key_exists('postalcode1', $_POST)) {
									print('value="'.htmlspecialchars($_POST['postalcode1'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['zip_1'].'" ';	
									}
								?>
								 />
								&nbsp;ー&nbsp;
								<input class="textbox zipcode2" type="text" id="postalcode2" name="postalcode2" placeholder="0000" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"　style="ime-mode: disabled"
								<?php
									if (array_key_exists('postalcode2', $_POST)) {
									print('value="'.htmlspecialchars($_POST['postalcode2'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['zip_2'].'" ';	
									}
								?>
								/>
								<div class="comment">
									郵便番号を入力いただくと住所が自動で入ります
								</div>
							</td>
						</tr>
						<tr>
							<td>
								都道府県<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<ul>
									<li class="clearfix2">
										<div class="select selectPref">	
											<select name="address1" id="address1" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" class="w-200">
											<?php
												$prefectures =
												array('北海道','青森県','岩手県','宮城県'
												,'秋田県','山形県','福島県','茨城県'
												,'栃木県','群馬県','埼玉県','千葉県'
												,'東京都','神奈川県','新潟県','富山県'
												,'石川県','福井県','山梨県','長野県'
												,'岐阜県','静岡県','愛知県','三重県'
												,'滋賀県','京都府','大阪府','兵庫県'
												,'奈良県','和歌山県','鳥取県','島根県'
												,'岡山県','広島県','山口県','徳島県'
												,'香川県','愛媛県','高知県','福岡県'
												,'佐賀県','長崎県','熊本県','大分県'
												,'宮崎県','鹿児島県','沖縄県');

												if (array_key_exists('address1', $_POST)) {
													$tmp_address1 = $_POST['address1'];
													print("<option value=\"\">都道府県を選択</option>\n");
												} else {
													$tmp_address1 = FALSE;
													print("<option value=\"\" selected>都道府県を選択</option>\n");
												}

												foreach ($prefectures as $val) {
													if ($val == $tmp_address1) {
														print("<option value=\"".$val."\" selected>".$val."</option>\n");
													} elseif($login_flag == true && $data['prefecture'] == $val){ 
														echo '<option value="'.$val.'" selected>'.$val.'</option>'."\n"; 
													} else {
														print("<option value=\"".$val."\">".$val."</option>\n");
													}
												}
											?>
											</select>							
										</div>
									</li>
								</ul>							
							</td>
						</tr>
						<tr>
							<td>
								市区<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<input class="textbox address2" type="text" name="address2" placeholder="（例）中央区" id="address2" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('address2', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['address2'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['address1'].'" ';	
									}
								?>
								/>	
							</td>
						</tr>
						<tr>
							<td>
								町村番地<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<input class="textbox txtWidMax" type="text" name="address3" placeholder="（例）箱崎町20-1　ポケカルマンション5階" id="address3" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('address3', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['address3'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['address2'].$data['address3'].'" ';	
									}
								?>
								/>
							</td>
						</tr>
						<tr>
							<td>
								電話番号<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<input class="textbox tel" type="text" id="telephone1" name="telephone1" placeholder="0000" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
								<?php
									if (array_key_exists('telephone1', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['telephone1'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['tel1_1'].'" ';	
									}
								?>
								/>
								-
								<input class="textbox tel" type="text" id="telephone2" name="telephone2" placeholder="0000" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
								<?php
									if (array_key_exists('telephone2', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['telephone2'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['tel1_2'].'" ';	
									}
								?>
								/>
								-
								<input class="textbox tel" type="text" id="telephone3" name="telephone3" placeholder="0000" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
								<?php
									if (array_key_exists('telephone3', $_POST)) {
									print(' value="'.htmlspecialchars($_POST['telephone3'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['tel1_3'].'" ';	
									}
								?>
								/>		
							</td>
						</tr>
						<tr>
							<td>
								Eメール<br>
								<span class="require2">
									必須
								</span>															
							</td>
							<td>
								<input class="textbox txtWidMax" type="text" name="mailaddress" placeholder="（例）abc@xxxx.com" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" 
								<?php
									if (array_key_exists('mailaddress', $_POST)) {
									print('value="'.htmlspecialchars($_POST['mailaddress'], ENT_QUOTES).'" ');
									}elseif($login_flag){
										echo 'value="'.$data['email'].'" ';	
									}
								?>
								/>
							</td>
						</tr>
						<tr>
							<td>
														
							</td>
							<td>
								<div class="tblComment">
									Eメールアドレスを誤って入力された場合、弊社からの予約完了・契約成立メールが受信できません。ご注意ください。ドメイン指定受信をされている場合、「@poke.co.jp」を追加ください。
								</div>
							</td>
						</tr>						
					</tbody>
				</table>
			</div>
	
			<h3 class="headline06">
				支払い方法
			</h3>
			<div class="carttableInfo">
				<?php
				if ($_SESSION['customer_payee'] == "2") {
				//顧客の支払先が「その他」→現地決済(固定)
				?>
					<div class="checkpayment">
						<input type="radio" name="paymethod" value="cash" id="paymethod-cash" checked="checked" />
						<label for="paymethod-cash">現地支払</label>
					</div>
				<?php
				}else{
					//顧客の支払先が「ぽけかる」→通常の決済処理
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
						if ($calcdays >= DUEBASEDATE  && $_SESSION['web_payment_type'] == "1") {
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
					if ($calcdays >= DUEBASEDATE  && $_SESSION['web_payment_type'] == "1") {
					}else{
					?>
						<div class="comment">
							※当商品のお支払いは、クレジットカードのみとなります。
						</div>
					<?php
					}
					?>
					<?php
					if($isJCOM) {
					?>
						<div class="comment">
							※クレジットカード決済ご希望の方は、確認画面後に、カード会社の決済画面に移動します。カード情報の入力をしていただきますが、ご参加決定後の決済となりますのでご安心くださいませ。
						</div>
					<?php
					}
				}
				?>				
			</div>
	
			<h3 class="headline08">
				その他
			</h3>
			<div class="carttableInfo">
				個人情報取り扱いについて<br />
				株式会社ポケカル(以下、｢当社」といいます。）は、個人情報(お客様の氏名、住所、電話番号、生年月日、性別など特定の個人を識別できるもの、以下も同様とします。）を取扱う際に、個人情報保護に関する諸法令、および主務大臣のガイドラインに定められた義務、並びに本ポリシーを遵守します。<br />
				<a href="/sp/privacy.html">詳しくはこちら >></a></p>
				<?php
				  if($isJCOM) {
				?>
					<br>
					お預かりした個人情報は、加入者確認のために（株）ジュピターテレコムに開示いたします。
					<br />上記の目的以外には、一切使用いたしません。
					<?php
						if (array_key_exists('personalinfo', $_POST)) {
							$tmp_personalinfo = $_POST['personalinfo'];
						} else {
							$tmp_personalinfo = "ng";
						}
					?>
					<div class="mb10"></div>
					<div class="checkItem">
						<input class="checkBox" type="radio" name="personalinfo" value="ok" id="personalinfo-ok" <?php if ($tmp_personalinfo == "ok") {print('checked="checked" ');}?>/>
						<label for="personalinfo-ok">同意する</label>　　　
						<input class="checkBox" type="radio" name="personalinfo" id="personalinfo-ng" value="ng" <?php if ($tmp_personalinfo != "ok") {print('checked="checked" ');}?>/>
						<label for="personalinfo-ng">同意しない</label>
					</div>
					
				<?php
				  }
				?>
				<br>
				割引・<?php if($isJCOM) { ?>新ツアー<?php }else{ ?>新企画<?php } ?>情報を<br />
				<?php
					if (array_key_exists('mailmag', $_POST)) {
					$tmp_mailmag = $_POST['mailmag'];
					} else {
					$tmp_mailmag = "ok";
					}
				?>
				<div class="checkItem">
					<input class="checkBox" type="radio" name="mailmag" value="ok" id="mailmag-ok" <?php if ($tmp_mailmag == "ok") {print('checked="checked" ');}?>/>
					<label for="mailmag-ok">もらう</label>　　　
					<input class="checkBox" type="radio" name="mailmag" id="mailmag-ng" value="ng" <?php if ($tmp_mailmag != "ok") {print('checked="checked" ');}?>/>
					<label for="mailmag-ng">もらわない</label>
				</div>
				<div class="mb10"></div>
				<p>
				*ポケカルの割引特典情報や<?php if($isJCOM) { ?>新ツアー<?php }else{ ?>新企画<?php } ?>情報を無料でお送りいたします。<br />
				*購読の解除はトップページのメルマガ専用フォームより行えます。
				</p>
				<?php
				  if($isJCOM) {
				?>
				<?php
				  if (array_key_exists('documentflg', $_POST)) {
					  $tmp_documentflg = $_POST['documentflg'];
				  } else {
					  $tmp_documentflg = "ok";
				  }
				?>
					<p>毎月発行「ポケカル」情報誌を
					<div class="checkItem">
						<input class="checkBox" type="radio" name="documentflg" value="ok" id="documentflg-ok" <?php if ($tmp_documentflg == "ok") {print('checked="checked" ');}?>/>
						<label for="documentflg-ok">発送希望</label>　　　
						<input class="checkBox" type="radio" name="documentflg" id="documentflg-ng" value="ng" <?php if ($tmp_documentflg != "ok") {print('checked="checked" ');}?>/>
						<label for="documentflg-ng">発送不要</label>
					</div>
					</p>
					<div class="mb10"></div>
					<p>*ポケカルが毎月発行しているツアーカタログ「ポケカル日帰り遊び」を無料でお送りいたします。<br />
					*発送解除はお電話・メール・FAXにて行えます。</p>
				<?php
				  }
				?>
			</div>

			<div class="wrapper">

				<p>
					<input type="hidden" name="member_id" value="<?php echo $member_id; ?>">

					<a href="#" class="btn-detail" onClick="document.frm_booking.submit();return false;">確認画面へ</a>
				</p>
			</div>
		
		</form>

<!-- ##### /BODY ##### --> 

  </section>
</div>
<!-- / #page -->




<script type="text/javascript">
<!--
$(document).ready(function() {
  //初期化
  if($('input[name="personalinfo"]:checked').val() === "ng"){
    $('#confirmButton').attr('disabled', true);
  }else{
    $('#confirmButton').attr('disabled', false);
    $('#confirmButton').removeAttr('disabled');
  }

  //radioボタンchangeイベント
  $('input[name="personalinfo"]:radio').live('change', function() {
    if($(this).val() === "ng"){
      $('#confirmButton').attr('disabled', true);
    }else{
      $('#confirmButton').attr('disabled', false);
      $('#confirmButton').removeAttr('disabled');
    }
  });
});
// -->
</script>

<script src="/sp/common/js/formInput.js?q=151211"></script>
<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
<script src="/sp/common/js/jquery.disableDoubleSubmit.js"></script>
<script>
$(function(){
  $("form").disableDoubleSubmit(3000);
});
</script>


<?php require_once("2018/footer.html"); ?>
<?php
    if(DEBUG){
	echo '$cookiedata[0]='.$cookiedata[0]."<br />";
	echo '$cookiedata[1]='.$cookiedata[1]."<br />";
	echo '$cookiedata[2]='.$cookiedata[2]."<br />";
	echo 'systemdate='.date('Y/m/d H:i:s')."<br />";
	echo '$diff='.$diff."<br />";
	echo '$cookiedata_first[0]='.$cookiedata_first[0]."<br />";
	echo '$cookiedata_first[1]='.$cookiedata_first[1]."<br />";
	echo '$cookiedata_first[2]='.$cookiedata_first[2]."<br />";
	echo '$_SESSION[\'currentasid\']='.$_SESSION['currentasid']."<br />";
	echo '$_SESSION[\'firstatid\']='.$_SESSION['firstatid']."<br />";
	echo '$_SESSION[\'firstasid\']='.$_SESSION['firstasid']."<br />";
	echo '$_SESSION[\'firstvisitday\']='.$_SESSION['firstvisitday']."<br />";
	echo '$_SERVER[\'SERVER_PORT\']='.$_SERVER['SERVER_PORT']."<br />";
        echo '$_SESSION[\'auto_furiwake\']='.$_SESSION['auto_furiwake']."<br />";
        echo '$_SESSION[\'lang\']='.$_SESSION['lang']."<br />";
    }
?>
