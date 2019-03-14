<?php
/**
 * 予約入力画面
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
$screenid_before = SCREEN_ID_CARDSECURITYINPUT;
$filename_before = FILENAME_CARDSECURITYINPUT;

// 自画面
$screenid_current = SCREEN_ID_CARDPREVIEW;
$filename_current = FILENAME_CARDPREVIEW;

// 次画面
$screenid_next = SCREEN_ID_CARDRESULT;
$filename_next = FILENAME_CARDRESULT;


$user_error = array();
$system_error = array();

// 正規表現エンコーディング指定
mb_regex_encoding('UTF-8');

/*
 * 言語情報の設定
 */
$transl = new Transl($_SESSION['lang']);
$langtbl = $transl->DetailTable;
$hissu = "";
if($transl->lang == RTS_LANG_JAPANESE){
  $hissu = "[必須]";
}else{
  $hissu = "*";
}
$urlroot = '/'.$transl->rootdir;
$incdir = $transl->rootdir;

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
                if(!$transl->isJapanese()){
                  $locate = "Location: " .$url_currentdir.FILENAME_TIMEOUT_ENGLISH;
                }  else {
                  $locate = "Location: " .$url_currentdir.FILENAME_TIMEOUT;
                }
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
	    // 次画面にリダイレクト
            $locate = "Location: " .$url_currentdir.$filename_next;
            header("HTTP/1.0 301 Moved Permanently");
            header($locate);
            exit();
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

    break;

    default:
        // 遷移元がその他の場合

        // 不正操作エラー画面にリダイレクト
        if(!$transl->isJapanese()){
          $locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL_ENGLISH;
        }else{
          $locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL;
        }
        header("HTTP/1.0 301 Moved Permanently");
        header($locate);
        exit();

    break;

}

//特定のASID
$isJCOM = false;
if($_SESSION['currentasid'] == ASID_JCOM) {
  $isJCOM = true;
}

/**
 * データ入力チェック
 */
function checkPostData(&$user_error) {

    // カード確認番号/cardkakuninno/必須
    //isParamCheckOK($user_error, $_POST['cardkakuninno'], 'カード確認番号', true, 0, 0, '', '^[0-9]+$');
}

/**
 * データ格納
 */
function storePostData() {
    //$_SESSION['cardkakuninno'] = $_POST['cardkakuninno'];
}

/**
 * データクリア
 */
function clearData() {
    //$_SESSION['cardkakuninno'] = '';
}

/**
 * POSTパラメータフィルタリング処理
 */
function filterPostData() {
    //filterControlChar($_POST['cardkakuninno']);
}

//予約入力設置用タグ2012.10.11
$tag_conv20121011 =<<< DOC
<!-- Google Code for &#9733;&#9733;&#20104;&#32004;&#20869;&#23481;&#20837;&#21147; -->
<!-- Remarketing tags may not be associated with personally identifiable information or placed on pages related to sensitive categories. For instructions on adding this tag and more information on the above requirements, read the setup guide: google.com/ads/remarketingsetup -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1004406842;
var google_conversion_label = "Hx1aCP6l6QMQupD43gM";
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/1004406842/?value=0&amp;label=Hx1aCP6l6QMQupD43gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

DOC;

//オーソリ金額を算出
$totalfee = $_SESSION['calcres'];
//カード確認番号伏字
$cardkakuninno = "";
for ($i=0;$i < strlen($_SESSION['cardkakuninno']);$i++){
  $cardkakuninno .="※";
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<title>Reservation | Pokekaru-Club</title>

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
<script src="/sp<?=$urlroot?>common/js/share.js"></script>
<script src="/sp<?=$urlroot?>common/js/script.js"></script>
<script src="/sp<?=$urlroot?>common/js/formInput.js"></script>
<script src="https://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/ajaxzip3-https.js" charset="UTF-8"></script>
<script src="/sp<?=$urlroot?>common/js/jquery.disableDoubleSubmit.js"></script>
<script>
$(function(){
  $("form").disableDoubleSubmit(3000);
});
</script>

<meta name="format-detection" content="telephone=no" />

</head>
<body>
<?php include_once("/_data/tags/body_tag.php"); ?>
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
			イベント・ツアー予約(カード情報確認)
		</h2>
		
		<ol class="cart_stepbar">
			<li><span>1</span></li>
			<li><span>2</span></li>
			<li><span>3</span></li>
			<li><span>4</span></li>
			<li class="visited"><span>5</span></li>
			<li><span>6</span></li>
		</ol>

		<div class="carttableTop">
			<?php
			if($isJCOM) {
			?>
				<div style="color:red">※本ツアーは多数の応募が想定されるため、ツアー予約後に抽選を行い、当選した方のみご参加頂けます。</div>
				<div style="color:red">※抽選結果は、後日メールにて全申込者にご連絡いたします。</div>
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
				
			<div class="mb40"></div>
			
			<p class="wrapper mb40">
				<a href="#" class="btn-detail" onClick="document.frm_booking.submit();return false;">決済確定する</a>
			</p>	
					
			<h3 class="headline06">
				<?=$transl->printL('chumonnaiyo')?>
			</h3>
			
			<div class="mb10"></div>
			<h4 class="headline07">
				<?=$transl->printL('shiharaikingaku')?>
			</h4>
			<div class="carttableInfo">
				<?php print($totalfee); ?><?=$transl->printL('yen')?>
			</div>			
			
			<div class="mb10"></div>
			<h4 class="headline07">
				<?=$transl->printL('shiharaihoho')?>
			</h4>
			<div class="carttableInfo">
				<?=$transl->printL('kurezittoka-do')?>
				<div class="mb5"></div>
				<div class="comment">
					※お支払い回数は、1回払いのみとなります
				</div>
			</div>	
			
			
			<div class="mb10"></div>
		
			<h3 class="headline06">
				<?=$transl->printL('ka-dojohonyuryoku')?>
			</h3>		
			
			<div class="mb10"></div>
			<h4 class="headline07">
				<?=$transl->printL('ka-dobango')?>
			</h4>
			<div class="carttableInfo">
				<?php print($_SESSION['cardno1']); ?>-※※※※-※※※※-<?php print($_SESSION['cardno4']); ?>
			</div>	
			
			<div class="mb10"></div>
			<h4 class="headline07">
				<?=$transl->printL('yukokigen')?>
			</h4>
			<div class="carttableInfo">
				<?php print($_SESSION['cardmonth']); ?>/<?php print($_SESSION['cardyear']); ?>(MONTH/YEAR)
			</div>		
			
			<div class="mb10"></div>
			<h4 class="headline07">
				<?=$transl->printL('ka-dokakuninbango')?>
			</h4>
			<div class="carttableInfo">
				<?php print($cardkakuninno); ?>
			</div>
		</form>
		
		
	</section>

</div><!-- / .bottom -->

<!-- ##### /BODY ##### --> 


</div>
<!-- / #page -->

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
        echo '$_SESSION[\'auto_furiwake\']='.$_SESSION['auto_furiwake']."<br />";
    }
?>
