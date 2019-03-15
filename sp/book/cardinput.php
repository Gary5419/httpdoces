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
$screenid_before = SCREEN_ID_BOOKAGREE;
$filename_before = FILENAME_BOOKAGREE;
$screenid_before2 = SCREEN_ID_BOOKAGREE2;
$filename_before2 = FILENAME_BOOKAGREE2;

// 自画面
$screenid_current = SCREEN_ID_CARDINPUT;
$filename_current = FILENAME_CARDINPUT;

// 次画面
$screenid_next = SCREEN_ID_CARDSECURITYINPUT;
$filename_next = FILENAME_CARDSECURITYINPUT;


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
    if ($_SESSION['screenid'] == $screenid_before || $_SESSION['screenid'] == $screenid_before2) {
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
    global $transl;

    $lang = $transl->lang;
    if(!$transl->isJapanese()){
      // カード番号/cardno1/必須
      isTranslParamCheckOK($lang, $user_error, $_POST['cardno1'], 'Card number1', true, 0, 0, '', '^[0-9]+$');
      // カード番号/cardno2/必須
      isTranslParamCheckOK($lang, $user_error, $_POST['cardno2'], 'Card number2', true, 0, 0, '', '^[0-9]+$');
      // カード番号/cardno3/必須
      isTranslParamCheckOK($lang, $user_error, $_POST['cardno3'], 'Card number3', true, 0, 0, '', '^[0-9]+$');
      // カード番号/cardno4/必須
      isTranslParamCheckOK($lang, $user_error, $_POST['cardno4'], 'Card number4', true, 0, 0, '', '^[0-9]+$');
      // 有効期限/cardyear/必須
      isTranslParamCheckOK($lang, $user_error, $_POST['cardyear'], 'Expiration date (Year)', true, 2, 2, '', '^[0-9]+$');
      // 有効期限/cardmonth/必須
      isTranslParamCheckOK($lang, $user_error, $_POST['cardmonth'], 'Expiration date (Month)', true, 2, 2, '', '^[0-9]+$');
    }else{
      // カード番号/cardno1/必須
      isParamCheckOK($user_error, $_POST['cardno1'], 'カード番号(1)', true, 0, 0, '', '^[0-9]+$');
      // カード番号/cardno2/必須
      isParamCheckOK($user_error, $_POST['cardno2'], 'カード番号(2)', true, 0, 0, '', '^[0-9]+$');
      // カード番号/cardno3/必須
      isParamCheckOK($user_error, $_POST['cardno3'], 'カード番号(3)', true, 0, 0, '', '^[0-9]+$');
      // カード番号/cardno4/必須
      isParamCheckOK($user_error, $_POST['cardno4'], 'カード番号(4)', true, 0, 0, '', '^[0-9]+$');
      // 有効期限/cardyear/必須
      isParamCheckOK($user_error, $_POST['cardyear'], '有効期限(年)', true, 2, 2, '', '^[0-9]+$');
      // 有効期限/cardmonth/必須
      isParamCheckOK($user_error, $_POST['cardmonth'], '有効期限(月)', true, 2, 2, '', '^[0-9]+$');
    }
    
}

/**
 * データ格納
 */
function storePostData() {
    $_SESSION['cardno1'] = $_POST['cardno1'];
    $_SESSION['cardno2'] = $_POST['cardno2'];
    $_SESSION['cardno3'] = $_POST['cardno3'];
    $_SESSION['cardno4'] = $_POST['cardno4'];
    $_SESSION['cardyear'] = $_POST['cardyear'];
    $_SESSION['cardmonth'] = $_POST['cardmonth'];
}

/**
 * データクリア
 */
function clearData() {
    $_SESSION['cardno1'] = '';
    $_SESSION['cardno2'] = '';
    $_SESSION['cardno3'] = '';
    $_SESSION['cardno4'] = '';
    $_SESSION['cardyear'] = '';
    $_SESSION['cardmonth'] = '';
}

/**
 * POSTパラメータフィルタリング処理
 */
function filterPostData() {
    filterControlChar($_POST['cardno1']);
    filterControlChar($_POST['cardno2']);
    filterControlChar($_POST['cardno3']);
    filterControlChar($_POST['cardno4']);
    filterControlChar($_POST['cardyear']);
    filterControlChar($_POST['cardmonth']);
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
      <?php include_once("tags/head_tag.php"); ?>

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
			イベント・ツアー予約(カード番号)
		</h2>
		
		<ol class="cart_stepbar">
			<li><span>1</span></li>
			<li><span>2</span></li>
			<li><span>3</span></li>
			<li class="visited"><span>4</span></li>
			<li><span>5</span></li>
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
			
			<div class="mb10"></div>
		
			<h3 class="headline06">
				お支払い金額
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
			
			<h3 class="headline06">
				<?=$transl->printL('ka-dojohonyuryoku')?>
			</h3>
			
			<div class="mb10"></div>
			
			<h4 class="headline07">
				<?=$transl->printL('ka-dobango')?>
				<span class="require">
					必須
				</span>
			</h4>
			
			<div class="carttableInfo">
				<input class="textbox cardnum" type="text" id="cardno1" name="cardno1" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
				<?php
				if (array_key_exists('cardno1', $_POST)) {
					print('value="'.htmlspecialchars($_POST['cardno1'], ENT_QUOTES).'" ');
				}
				?>
				size="4" />

				<input class="textbox cardnum" type="text" id="cardno2" name="cardno2" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
				<?php
				if (array_key_exists('cardno2', $_POST)) {
					print('value="'.htmlspecialchars($_POST['cardno2'], ENT_QUOTES).'" ');
				}
				?>
				size="4" /> 
				
				<input class="textbox cardnum" type="text" id="cardno3" name="cardno3" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
				<?php
				if (array_key_exists('cardno3', $_POST)) {
				print('value="'.htmlspecialchars($_POST['cardno3'], ENT_QUOTES).'" ');
				}
				?>					 
				size="4" />
				
				<input class="textbox cardnum" type="text" id="cardno4" name="cardno4" maxlength="4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onChange="zenhan(this);"
				<?php
				if (array_key_exists('cardno4', $_POST)) {
					print('value="'.htmlspecialchars($_POST['cardno4'], ENT_QUOTES).'" ');
				}
				?>
				size="4" />
				
				<div class="mb10"></div>
				
				<div class="comment">
					カードの裏面に記載されたカード番号を半角数字で左詰めスペースなしにてご入力下さい。
				</div>
				
				<div class="mb10"></div>
				
				<img src="/images/book/cardbrands.gif" width="100%" style="display:inline;" />
				
				<div class="mb10"></div>
				<div class="comment">
					<span style="color: #FF0000">
						※AMEXカードをご利用の場合、下記のとおり、4桁-4桁-4桁-3桁でご入力ください。
					</span>
					<br>
					（例）AMEXカードの入力例・・【1234】-【5678】-【9123】-【456】
					<div class="mb5"></div>
					<span style="color: #FF0000">
						※ダイナースカードをご利用の場合、下記のとおり、 4桁-4桁-4桁-2桁でご入力ください。
					</span>
					<br>
					（例）ダイナースカードの入力例・・【1234】-【5678】-【9123】-【45】
					
					
				</div>
				
				
			</div>
			
			<div class="mb10"></div>
			
			<h4 class="headline07">
				<?=$transl->printL('yukokigen')?>
				<span class="require">
					必須
				</span>
			</h4>
			
			<div class="carttableInfo">
				<ul>
					<li class="clearfix">
						<div class="select selectYm">
							<select name="cardmonth" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
							<?php
							$cardmonth = array();
							for($i=0;$i < 12;$i++){
								array_push($cardmonth, mb_substr('0'.($i+1),-2));
							}
							if (array_key_exists('cardmonth', $_POST)) {
								$tmp_cardmonth = $_POST['cardmonth'];
								print("<option value=\"\"></option>\n");
							} else {
								$tmp_cardmonth = FALSE;
								print("<option value=\"\" selected></option>\n");
							}

							foreach ($cardmonth as $val) {
								if ($val == $tmp_cardmonth) {
									print("<option value=\"".$val."\" selected>".$val."</option>\n");
								} else {
								  	print("<option value=\"".$val."\">".$val."</option>\n");
								}
							}
							?>
							</select>
						</div>
						<p class="txt04">
							／
						</p>
						<div class="select selectYm">
							<select name="cardyear" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)">
								<?php
								$year = intval(date('y'));
								$years = array();
								for($i=0;$i < 20;$i++){
									array_push($years, mb_substr('0'.($year+$i),-2));
								}
								if (array_key_exists('cardyear', $_POST)) {
									$tmp_year = $_POST['cardyear'];
									print("<option value=\"\"></option>\n");
									} else {
									$tmp_year = FALSE;
									print("<option value=\"\" selected></option>\n");
								}

								foreach ($years as $val) {
									if ($val == $tmp_year) {
										print("<option value=\"".$val."\" selected>".$val."</option>\n");
									} else {
										print("<option value=\"".$val."\">".$val."</option>\n");
									}
								}
								?>
							</select>
						</div>
						<p class="txt05">
							(MONTH/YEAR)
						</p>
					</li>
				</ul>
				<div class="mb5"></div>
				<div class="comment">
					<?=$transl->printL('ka-doyukokigensetsumei')?>
				</div>
			</div>
			
			<div class="wrapper">
				<p onclick="return judge()">
					<a id="p1" class="btn-detail" onClick="document.frm_booking.submit();return false;">次へ</a>
				</p>
			</div>			
			
		</form>
		
		
		
	</section>


<!-- ##### /BODY ##### --> 

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
