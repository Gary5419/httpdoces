<?php
require_once 'MDB2.php';

require 'include/const.inc.php';
require 'include/common.inc.php';
require_once 'include/pokebook.inc.php';

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = KUCHIKOMI_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

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
	    $error_msg = "クチコミ登録エラー。商品番号が指定されていません。";
	    // ログ出力
	    logOnline($error_msg, ONLINE_LOG_LEVEL_FATAL);
	    // システムエラー画面にリダイレクト
	    $locate = "Location: " .$url_currentdir.FILENAME_ERROR;
	    header("HTTP/1.0 301 Moved Permanently");
	    //header($locate);
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

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />

<title>口コミ登録｜ぽけかる倶楽部</title>

<meta name="viewport" content="width=320" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/sp/common/js/google-analytics.js"></script>
<script src="/sp/common/js/share.js"></script>
<script src="/sp/common/js/script.js"></script>

<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->

<link rel="stylesheet" href="/sp/common/css/import.css" />
    <?php include_once("tags/head_tag.php"); ?>
</head>
<body id="kuchikomi">
<?php include_once("tags/body_tag.php"); ?>
<div id="page">

<?php require_once "header.inc"; ?>

<section class="headline">
<h1>口コミ登録</h1>
<ul>
<li><a href="/sp/">TOP</a></li>
<li>口コミ登録</li>
</ul>
</section><!-- / .headline -->
<div id="top">
<p class="title">
弊社の<?php print(htmlspecialchars($eventname));?>にご参加頂き、誠にありがとうございました。</p>

<p class="f-13">ご意見、ご感想をお聞かせ下さい。今後のサービス開発の参考にさせていただきます。<br>
※なお頂いたご意見・ご感想はクチコミとして匿名で掲載させて頂きます。</p>

</div>
<form method="POST" action="<?php print($url_currentdir.$filename_current); ?>" name="comments_form" id="comments-form">
<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
<input type="hidden" name="entry_id" value="<?php print(htmlspecialchars($entryid));?>" />
<input type="hidden" name="kuchikomi_event_id" value="<?php print(htmlspecialchars($eventid));?>" />

<div id="event" class="bookbox">

<h3>年齢</h3>
<p>
<select name="comment_custom_combo2">
<option value="10代">10代</option>
<option value="20代">20代</option>
<option value="30代">30代</option>
<option value="40代">40代</option>
<option value="50代">50代</option>
<option value="60代">60代</option>
<option value="70代">70代</option>
<option value="80代">80代</option>
</select>
</p>

<h3>性別</h3>
<p>
<select name="comment_custom_combo1">
<option value="男性">男性</option>
<option value="女性">女性</option>
</select>
</p>

<h3>ご意見・ご感想</h3>
<p>
<textarea id="comment-text" name="text"></textarea>
</p>

</div><!-- /#event -->

<div class="preview">
<p class="t-a-c m-b-15">
<a href="javascript:void(0)" onclick="comments_form.submit();">
<img src="/sp/common/img/btn_preview2.png" width="271" height="58" alt="確認画面へ" />
</a>
</p>
</div>

</form>

<div class="bottom">

<p class="btn"><a href="#page"><img src="/sp/common/img/bottom_point.png" width="18" height="20">ページの先頭へ戻る</a></p>

<p class="btn"><a href="/sp/">TOPページへ戻る</a></p>

</div><!-- / .bottom -->

<?php require_once "footer_nopclink.inc"; ?>

</div>
<!-- / #page -->



</body>
</html>