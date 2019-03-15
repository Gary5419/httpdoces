<?php
/**
 * カード決済確認画面
 *
 *
 */
require_once("include/2018/config.php");	#リニューアル時追加
#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/pokebook.inc.php';
require 'include/template_book.inc.php';  //add sjm.tk

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

// カレントディレクトリ
$url_currentdir = BOOKING_URL_SCHEME.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';

// 前画面
$screenid_before = SCREEN_ID_PREVIEW;
$filename_before = FILENAME_PREVIEW;

// 自画面
$screenid_current = SCREEN_ID_PREVIEW_CARD;
$filename_current = FILENAME_PREVIEW_CARD;

$system_error = array();

// 前画面チェックOK遷移判定
if ($_SESSION['screenid'] == $screenid_before) {
    // 前画面チェックOK遷移

} else {
    // 前画面チェックOK遷移以外

    // 不正操作画面にリダイレクト
    $locate = "Location: " .$url_currentdir.FILENAME_ILLEGAL;
    header("HTTP/1.0 301 Moved Permanently");
    header($locate);
    exit();
}
RecLog($_SESSION['stock_id']);
// 催行日フォーマット変更
$ss_day = $_SESSION['saikouday'];
$temp_day = mktime(0, 0, 0, substr($ss_day, 4, 2), substr($ss_day, 6, 2), substr($ss_day, 0, 4));
$cond_day = date('Y-m-d', $temp_day);

// イベント名,キャンセル規定区分はSESSIONパラメータから取得
//     画面出力の際はサニタイズを忘れないこと。
$eventname      = $_SESSION['eventname'];

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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja" xml:lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>予約｜ぽけかる倶楽部</title>
<meta name="description" content="" />
<meta name="keywords" content="" />
<meta name="robots" content="noydir" />
<meta name="robots" content="noodp" />
<meta content="text/javascript" http-equiv="Content-Script-Type" />
<meta content="text/css" http-equiv="Content-Style-Type" />

<link href="/css/book.css" rel="stylesheet" type="text/css" />

<link rel="stylesheet" href="/common2/css/font-awesome.min.css">
<link rel="stylesheet" href="/common2/css/jquery-ui.css">
<link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
<link rel="stylesheet" href="/common2/css/slick.css">
<link rel="stylesheet" href="/common2/css/style.css">

<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="cleartype" content="on">
<![endif]-->
    <?php include_once("tags/head_tag.php"); ?>


<link href="/common/css/errstyle.css" type="text/css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/common/js/heightLine.js"></script>
<script type="text/javascript" src="/common/js/valueClear.js"></script>
<script type="text/javascript" src="/common/js/pageTop.js"></script>
<script type="text/javascript" src="/common/js/formInput.js"></script>
<SCRIPT language="JavaScript" type="text/javascript">
<!-- Overture K.K.
window.ysm_customData = new Object();
window.ysm_customData.conversion = "transId=,currency=,amount=";
var ysm_accountid  = "1IH9O7LUPVT0APUMU5PENMR659O";
document.write("<SCR" + "IPT language='JavaScript' type='text/javascript' " 
+ "SRC=//" + "srv2.wa.marketingsolutions.yahoo.com" + "/script/ScriptServlet" + "?aid=" + ysm_accountid 
+ "></SCR" + "IPT>");
// -->
</SCRIPT>
</head>
<body onLoad="f_ChangeInputColor()">
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>



      <div class="main">
        <div class="wrapper">

  <!-- ##### BODY ##### -->
  <div id="bodyField">
    <div>
      <div id="mainField">
        <!-- ##### BREADCRUMBS ##### -->
        

          <ul class="breadcrumb">
            <li><a class="trans" href="/">ポケカルTOP</a></li>
            <li>
			<?php if($isJCOM) { ?>
              イベント・ツアー予約(抽選) 
              <?php }else{ ?>
              イベント・ツアー予約 
              <?php } ?>
            </li>
          </ul>


<?php
if (DEBUG) {
    $a8requesturl = 'http://'.$_SERVER['HTTP_HOST'];
	$sonetrequesturl = 'http://'.$_SERVER['HTTP_HOST'];
	$clossarequesturl = 'http://'.$_SERVER['HTTP_HOST'];
	$uncutrequesturl = 'http://'.$_SERVER['HTTP_HOST'];
	$valuerequesturl = 'http://'.$_SERVER['HTTP_HOST'];
	$sonet7requesturl = 'http://'.$_SERVER['HTTP_HOST'];
} else {
    $a8requesturl = A8_FLY_TAG_URL;
	$sonetrequesturl = 'https://www.scadnet.com/ac/action.php';
	$clossarequesturl = 'https://www.cross-a.net/xa.php';
	$uncutrequesturl = 'https://www.motionlink.jp/act.php';
	$valuerequesturl = 'https://itrack2.valuecommerce.ne.jp/cgi-bin/2640965/vc_itag.cgi';
	$sonet7requesturl = 'https://www.scadnet.com/ac/action.php';
}
    // 注文金額の算出
    $totalfee =  $_SESSION['adultfee'] * ($_SESSION['mannum'] + $_SESSION['womannum']);
    $totalfee += $_SESSION['childfee'] * ($_SESSION['boynum'] + $_SESSION['girlnum']);

?>

<div id="container">

<div id="reservation">

<!-- <form action="/"> -->
<table class="entryform">
<tr>
<th>イベント名</th>
<td><strong><?php print(htmlspecialchars($eventname, ENT_QUOTES));?></strong></td>
</tr>
<tr>
<th>お支払金額</th>
<td>
    <table class="inner">
    <tr>
    		<th>大人　　
			<span class="price">
				<?php 
					//割引が適用されているか判断
					if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
						print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['baseadultfee'].")", ENT_QUOTES));
					} else {
						print(htmlspecialchars($_SESSION['adultfee'], ENT_QUOTES));
					}
				?></font>円
			</span>
		</th>
    <td> <?php print(htmlspecialchars($_SESSION['mannum'], ENT_QUOTES));?> 人</td>
    </tr>
<?php
// 子供料金未設定の判断
if (!$childfee_unsetflg) {
?>
    <tr>
		<th>子供　　
			<span class="price">
				<?php 
					//割引が適用されているか判断
					if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
						print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['basechildfee'].")", ENT_QUOTES));
					} else {
						print(htmlspecialchars($_SESSION['childfee'], ENT_QUOTES));
					}
				?></font>円
			</span>
		</th>
    <td> <?php print(htmlspecialchars($_SESSION['boynum'], ENT_QUOTES));?> 人</td>
    </tr>
<?php
}
?>

<?php
	//提携サイト割引適用対応
	//割引が適用されているか判断
	if($_SESSION['adultfee'] != $_SESSION['baseadultfee']) {
		//画面表示
?>
		<th colspan="3">合計　　
			<span class="price">
				<?php
					print(htmlspecialchars($_SESSION['calcres'] , ENT_QUOTES)."<font Color=\"black\">(".htmlspecialchars($_SESSION['basecalcres'] , ENT_QUOTES).")");
				?></font>円
			</span>
		</th>
<?php
	} else {
?>
	<th>合計　　
		<span class="price">
			<?php
				print(htmlspecialchars($_SESSION['calcres'] , ENT_QUOTES));
			?>円
		</span>
	</th>
<?php
	}
?>
        <?php
        //イベントカテゴリがチケットであれば送料発生
        if($_SESSION['category_id'] == CATEGORY_TICKET) {
        ?>
		<tr>
		<td colspan="2">
        	<span class="x-small"><strong>※当イベントはチケット送料が
        <?php
                print($_SESSION['postage']);
        ?>
       		円発生します。あらかじめご了承ください。
        	</strong></span>
		</td>
		</tr>
	<?php
        }
        ?>
    </table>
</td>
</tr>
</table>
</form>

</br>
<?php

$kokyaku_id = $_SESSION['bookingnumber'];
$chumon_no = $_SESSION['bookingnumber'];
$kingaku = htmlspecialchars($_SESSION['calcres'] , ENT_QUOTES);

$membershopid = mb_convert_encoding(MEMBERSHOPID,"UTF-8","SJIS");
$certifycode = mb_convert_encoding(CERTIFYCODE,"UTF-8","SJIS");
$backurl = mb_convert_encoding(BACKURL,"UTF-8","SJIS");
$kokyaku_id = mb_convert_encoding($kokyaku_id,"UTF-8","SJIS");
$chumon_no = mb_convert_encoding($chumon_no,"UTF-8","SJIS");
$kingaku = mb_convert_encoding($kingaku,"UTF-8","SJIS");

//print("<form action=".AUTHURL." method=\"post\" target=\"_self\">");
$form = "<form action=".AUTHURL." method=\"post\" target=\"_self\">"."\n";
$form .= "<input type=\"hidden\" value=".$membershopid." name=\"in_kamei_id\" />"."\n";
$form .= "<input type=\"hidden\" value=".$certifycode." name=\"in_n\" />"."\n";
$form .= "<input type=\"hidden\" value=".$backurl." name=\"inredirecturl_ok\" />"."\n";
$form .= "<input type=\"hidden\" value=".$backurl." name=\"inredirecturl_ng\" />"."\n";
$form .= "<input type=\"hidden\" value=".$backurl." name=\"inredirecturl_can\" />"."\n";
$form .= "<input type=\"hidden\" value=".$kokyaku_id." name=\"in_kokyaku_id\" />"."\n";
$form .= "<input type=\"hidden\" value=".$chumon_no." name=\"in_chumon_no\" />"."\n";
$form .= "<input type=\"hidden\" value=".$kingaku." name=\"in_kingaku\"/>";

print($form);

?>

<fieldset>
<p id="warning_txt">以上の内容でカード決済を行います。</p>
<div class="button"><input type="submit" value="カード決済ページへ" /></div>
</fieldset>
</form>


<?php
$form = "<form action=".$url_currentdir.FILENAME_BOOKINPUT_CARD." method=\"post\" target=\"_self\">"."\n";
$form .= "<input type=\"hidden\" value=".$screenid_current." name=\"screenid\"/>";

print($form);

?>
<fieldset>
<p id="warning_txt">カードでの支払をやめる場合は下記ボタンをクリックしてください。</p>
<div style="text-align:center"><font size = "3">※申込入力画面へ戻ります。クレジットカードを<u>利用しない</u>を選択してください。</font><div>
<div class="button"><input type="submit" value="申込ページへ" /></div>
</fieldset>
</form>

</div><!-- //#reservation -->

</div><!-- //#container -->

      <?php include_once("include/2018/footer.html"); ?>