<?php
require_once 'MDB2.php';

require 'include/const.inc.php';
require 'include/common.inc.php';
require_once 'include/pokebook.inc.php';

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

//イベントid取得
$eventid = null;
$entryid = null;

// 自画面
$screenid_current = SCREEN_KUCHIKOMI_INPUT;
$filename_current = FILENAME_KUCHIKOMI_INPUT;

// 次画面
$screenid_next = SCREEN_KUCHIKOMI_PREVIEW;
$filename_next = FILENAME_KUCHIKOMI_PREVIEW;

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
    $past_screen_ind = PAST_SCREEN_IND_OTHER;
}

switch ($past_screen_ind) {
    case PAST_SCREEN_IND_SELF:
        // 遷移元が自画面の場合
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
	    // 次画面にリダイレクト
	    $locate = "Location: " .$url_currentdir.$filename_next;
	    header("HTTP/1.0 301 Moved Permanently");
	    header($locate);
	    exit();
        } else {                    // チェックNG
            // 表示のみの制御なので、ここでは何もしない
        }
	break;
    case PAST_SCREEN_IND_OTHER:
	// 遷移元がその他の場合
	if(isset($_GET['eventid']) && !cmIsEmpty($_GET['eventid'])){
		$eventid = $_GET['eventid'];
	}else{
	    // ログ出力メッセージ
	    $error_msg = "クチコミ登録エラー。商品番号からイベント情報が取得できません。";
	    // ログ出力
	    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL);
	    // システムエラー画面にリダイレクト
	    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	    header("HTTP/1.0 301 Moved Permanently");
	    header($locate);
	    exit();
	}
	break;
}

// MDB2:: factory() を使用して、インスタンスの作成と、ホストへの接続を行う
$mdb2 =& MDB2:: factory(DB_DSN);
if (PEAR:: isError($mdb2)) {
    //die($mdb2->getMessage());
	//ログの出力を行い、エラーページへ遷移
}
$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

//SQL文 イベントマスタからイベントIDのエントリーIDを取り出す
$sql = "SELECT entryid,eventname
FROM ".MTDB.".m_event WHERE eventid='".$eventid."'";

//SQL文を実行する
$res =& $mdb2->query($sql);
// 結果がエラーでないかどうかを常にチェック
if (PEAR:: isError($res)) {
    //die($res->getMessage());
	//ログの出力を行い、エラーページへ遷移
}

//取得できなければエラー
if($res->numRows() < 1){
    // ログ出力メッセージ
    $error_msg = "クチコミ登録エラー。商品番号からイベント情報が取得できません。";
    // ログ出力
    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL);
    // システムエラー画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}

//２件以上存在したら後優先
while($row = $res->fetchRow()){
     $entryid = $row['entryid'];
     $eventname= $row['eventname'];
}

//結果セットの開放	
$res->free();

//コネクションの破棄
$mdb2->disconnect();

/**
 * データ入力チェック
 */
function checkPostData(&$user_error) {
    //チェック処理なし
}

/**
 * データ格納
 */
function storePostData() {
    $_SESSION['entry_id'] = $_POST['entry_id'];
    $_SESSION['kuchikomi_event_id'] = $_POST['kuchikomi_event_id'];
    $_SESSION['text'] = $_POST['text'];
    $_SESSION['comment_custom_combo2'] = $_POST['comment_custom_combo2'];
    $_SESSION['comment_custom_combo1'] = $_POST['comment_custom_combo1'];
}

/**
 * データクリア
 */
function clearData() {
    $_SESSION['entry_id'] = '';
    $_SESSION['kuchikomi_event_id'] = '';
    $_SESSION['text'] = '';
    $_SESSION['comment_custom_combo2'] = '';
    $_SESSION['comment_custom_combo1'] = '';
}

/**
 * POSTパラメータフィルタリング処理
 */
function filterPostData() {
    filterControlChar($_POST['entry_id']);
    filterControlChar($_POST['kuchikomi_event_id']);
    filterControlChar($_POST['text']);
    filterControlChar($_POST['comment_custom_combo2']);
    filterControlChar($_POST['comment_custom_combo1']);
}

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja" xml:lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>クチコミ登録|ぽけかる倶楽部</title>
    <meta name="description" content="クチコミ情報が満載!ポケットカルチャー。ツアー予約ならぽけかる倶楽部へ。クチコミもぜひご覧ください" />
    <meta name="keywords" content="クチコミ登録,日帰りツアー,体験イベント,現地集合,現地解散,ぽけかる倶楽部" />
    <meta name="robots" content="noydir" />
    <meta name="robots" content="noodp" />
    <meta content="text/javascript" http-equiv="Content-Script-Type" />
    <meta content="text/css" http-equiv="Content-Style-Type" />
    <link href="/common/css/base.css" type="text/css" rel="stylesheet" />
    <link href="/common/css/errstyle.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="/common/js/heightLine.js"></script>
    <script type="text/javascript" src="/common/js/valueClear.js"></script>
    <script type="text/javascript" src="/common/js/pageTop.js"></script>
    <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
<body id="index">
<?php include_once("/_data/tags/body_tag.php"); ?>
<div id="wrapper">

<h1>クチコミ登録｜現地集合・現地解散型 お手軽日帰りツアー・体験イベント</h1>

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

<div id="comment">弊社の<?php print(htmlspecialchars($eventname));?>にご参加頂き、誠にありがとうございました。<br />
ご意見・ご感想をお聞かせ下さい。今後のサービス開発の参考にさせていただきます。<br />
※なお頂いたご意見・ご感想はこクチコミとして匿名で掲載させて頂きます。</div>
     
           <form method="post" action="<?php print($url_currentdir.$filename_current); ?>" name="comments_form" id="comments-form">
		<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
		<input type="hidden" name="entry_id" value="<?php print(htmlspecialchars($entryid));?>" />
		<input type="hidden" name="kuchikomi_event_id" value="<?php print(htmlspecialchars($eventid));?>" />
<table class="entryform">
<tr>
<th>年齢</th>
<td>
        <select name="comment_custom_combo2" size="1" style="background:white">
        <option value="10代">10代</option>
        <option value="20代">20代</option>
        <option value="30代">30代</option>
        <option value="40代">40代</option>
        <option value="50代">50代</option>
        <option value="60代">60代</option>
        <option value="70代">70代</option>
        <option value="80代">80代</option>
        </select>
</td>
</tr>
<tr>
<th>性別</th>
<td>
        <select name="comment_custom_combo1" size="1" style="background:white">
        <option value="男性">男性</option>
        <option value="女性">女性</option>
        </select>
</td>
</tr>
<tr>
<th>ご意見・ご感想</th>
<td><textarea id="comment-text" name="text" rows="10" cols="60"></textarea></td>
</tr>
<tr>
</table>
<div class="button"><input type="submit" accesskey="v" name="preview_button" id="comment-preview" value="確認画面" /></div>

           </form>
   

</div><!-- //#inquiry -->

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