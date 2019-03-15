<?php
/**
 * クチコミ確認画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );

require_once 'MDB2.php';

require 'include/const.inc.php';
require 'include/common.inc.php';
require 'include/pokebook.inc.php';

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
$screenid_before = SCREEN_KUCHIKOMI_INPUT;
$filename_before = FILENAME_KUCHIKOMI_INPUT;

// 自画面
$screenid_current = SCREEN_KUCHIKOMI_PREVIEW;
$filename_current = FILENAME_KUCHIKOMI_PREVIEW;

// 次画面
$screenid_next = SCREEN_KUCHIKOMI_END;
$filename_next = FILENAME_KUCHIKOMI_END;

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
    }else {
        // 前画面チェックOK遷移以外
        $past_screen_ind = PAST_SCREEN_IND_OTHER;
    }
}

switch ($past_screen_ind) {
    case PAST_SCREEN_IND_SELF:
        // 遷移元が自画面の場合
        // session画面IDに自画面IDをセット
        $_SESSION['screenid'] = $screenid_current;
	$res = kuchikomiinsert($system_error);
	if($res){   //クチコミ登録成功
	    // 次画面にリダイレクト
	    $locate = "Location: " .$url_currentdir.$filename_next;
	    header("HTTP/1.0 301 Moved Permanently");
	    header($locate);
	    exit();
	}
	
	break;
    case PAST_SCREEN_IND_BEFORE:
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
 * クチコミ登録
 */
function kuchikomiinsert(&$system_error) {
    global $mdbwww;
    
    // SQL文
    $sql = 'INSERT INTO '.MTDB.'.mt_comment ';
    $sql .= '(comment_author';
    $sql .= ',comment_blog_id';
    $sql .= ',comment_created_on';
    $sql .= ',comment_email';
    $sql .= ',comment_entry_id';
    $sql .= ',comment_ip';
    $sql .= ',comment_modified_on';
    $sql .= ',comment_text';
    $sql .= ',comment_url';
    $sql .= ',comment_visible';
    $sql .= ',comment_custom_combo2';
    $sql .= ',comment_custom_combo1';
    $sql .= ') VALUES ';
    $sql .= '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $datatype = array('text', 'integer', 'text', 'text', 'integer', 'text', 'text', 'text', 'text', 'integer', 'text', 'text');
    // プリペア
    $stmt = $mdbwww->prepare($sql, $datatype, MDB2_PREPARE_MANIP);
    if (PEAR::isError($stmt)) {
        // ログ出力メッセージ
        $error_msg = "クチコミ登録SQLプリペアエラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, array($sql), __FILE__, __LINE__);
        return false;
    }
    $remoteIP = '';
    if(isset($_SERVER['REMOTE_ADDR'])){
	$remoteIP = $_SERVER['REMOTE_ADDR'];
    }
    // Data
    $data = array($_SESSION['comment_custom_combo1'].'/'.$_SESSION['comment_custom_combo2']
                 ,1
                 ,date('Y-m-d H:i:s')
		 ,''
		 ,$_SESSION['entry_id']
		 ,$remoteIP
		 ,date('Y-m-d H:i:s')
		 ,$_SESSION['text']
		 ,''
		 ,0
		 ,$_SESSION['comment_custom_combo2']
		 ,$_SESSION['comment_custom_combo1']
		);
    // SQL実行
    $result = $stmt->execute($data);
    if (PEAR::isError($result)) {
        // ログ出力メッセージ
        $error_msg = "クチコミ登録SQL実行エラー";
        // ログ出力
        logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL, $data, __FILE__, __LINE__);
        return false;
    }

    $stmt->free();
    return true;
}


$mdbwww->disconnect();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja" xml:lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>クチコミ確認|ぽけかる倶楽部</title>
    <meta name="description" content="クチコミ情報が満載!ポケットカルチャー。ツアー予約ならぽけかる倶楽部へ。クチコミもぜひご覧ください" />
    <meta name="keywords" content="クチコミ確認,日帰りツアー,体験イベント,現地集合,現地解散,ぽけかる倶楽部" />
    <meta name="robots" content="noydir" />
    <meta name="robots" content="noodp" />
    <meta content="text/javascript" http-equiv="Content-Script-Type" />
    <meta content="text/css" http-equiv="Content-Style-Type" />
    <link href="/common/css/base.css" type="text/css" rel="stylesheet" />
    <link href="/common/css/errstyle.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="/common/js/heightLine.js"></script>
    <script type="text/javascript" src="/common/js/valueClear.js"></script>
    <script type="text/javascript" src="/common/js/pageTop.js"></script>
    <?php include_once("tags/head_tag.php"); ?>
</head>
<body id="index">
<?php include_once("tags/body_tag.php"); ?>
<div id="wrapper">

<h1>クチコミ確認｜現地集合・現地解散型 お手軽日帰りツアー・体験イベント</h1>

<div id="header">
<div id="site-title"><a href="/"><img src="/common/img/sitetitle.gif" alt="ぽけかる倶楽部 " /></a></div>
<ul id="header-navi">
<li><a href="/tokutyo/index.html">ぽけかる倶楽部について</a></li>
<li><a href="/faq/index.html">よくある質問と回答</a></li>
<li><a href="/howto/index.html">お申込方法</a></li>
<li><a href="/sitemap.html">サイトマップ</a></li>
</ul>
</div><!-- //#header -->

<ul id="droplink">
<li><strong><b><a href="/">日帰りツアー・体験イベント トップ</a></b></strong>&nbsp;&nbsp;>&nbsp;クチコミ登録</li>
<li id="catch"><b><strong>日帰りツアー・体験イベント</strong></b></li>
</ul><!-- //#droplink -->

<div id="container">

<div id="inquiry">

<div id="comment">入力いただいた内容をご確認下さい。<br />
よろしければ、「投稿する」ボタンをおしてください。</div>

<form method="post" action="<?php print($url_currentdir.$filename_current); ?>" name="comments_form" id="comments-form">
    <input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />

<table class="entryform confirm">
<tr>
<th>年代</th>
<td><?php print(htmlspecialchars($_SESSION['comment_custom_combo2'], ENT_QUOTES)); ?></td>
</tr>
<tr>
<th>性別</th>
<td><?php print(htmlspecialchars($_SESSION['comment_custom_combo1'], ENT_QUOTES)); ?></td>
</tr>
<tr>
<th>ご意見・ご感想</th>
<td><?php print(nl2br(htmlspecialchars($_SESSION['text'], ENT_QUOTES))); ?></td>
</tr>
</table>

<div class="button"><input type="submit" accesskey="s" name="post" id="comment-submit" value="投稿する" /></div>

</form>

</div><!-- //#container -->
<div id="footer">


<div id="pageup"><a href="javascript:void(0);" onclick="pageTop(); return false;" onkeypress="0"><img src="/common/img/pageup.gif" alt="ページトップへ ▲" /></a></div>

<div id="common">
<div id="copyright">Copyright c 2009 Pokekaru-Club. All rights reserved.</div>
<ul>
<li><a href="/company.html">運営会社</a>&nbsp;｜&nbsp;</li>
<li><a href="/ryokogyo.html">旅行業登録票</a>&nbsp;｜&nbsp;</li>
<li><a href="/privacy.html">個人情報保護</a>&nbsp;｜&nbsp;</li>
<li><a href="/copyright.html">著作権</a>&nbsp;｜&nbsp;</li>
<li><a href="/yakkan.html">約款</a>&nbsp;｜&nbsp;</li>
<li><a href="/book/inquiry.php">お問い合わせ</a></li>
</ul>
</div>

</div><!-- //#footer -->

</div><!-- //#wrapper -->

</body>
</html>