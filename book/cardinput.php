<?php
/**
 * 予約入力画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );
require_once("include/2018/config.php");	#リニューアル時追加
#require_once 'MDB2.php';

#require 'include/const.inc.php';
#require 'include/common.inc.php';
#require 'include/pokebook.inc.php';
require 'include/template_book.inc.php';

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
  $hissu = "必須";
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<meta name="Description" content="" />
<meta name="Keywords" content="" />
<meta name="copyright" content="" />
<title>Reservation | Pokekaru-Club</title>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="<?=$urlroot?>common/js/heightLine.js"></script>
<script type="text/javascript" src="<?=$urlroot?>common/js/valueClear.js"></script>
<script type="text/javascript" src="<?=$urlroot?>common/js/pageTop.js"></script>
<script type="text/javascript" src="<?=$urlroot?>common/js/formInput.js"></script>

<style>
.submit_btn2 {
	-webkit-appearance:none;
	border:none;
    display: block;
    width: 410px;
    margin: 0 auto;
    margin-bottom: 15px;
    text-decoration: none;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    background: #FB8B64;
    color: #FFF;
	font-weight:bold;
    text-align: center;
    line-height: 40px;
    box-shadow: 0px 2px 0px #FE733B;
    -moz-box-shadow: 0px 2px 0px #FE733B;
    font-size: 18px;
}
.titlebar {
    width: 100%;
    background: #ECECEC;
    color: #404040;
    font-size: 20px;
    font-weight: bold;
    -moz-box-shadow: 0px 2px 0px #A6A6A6;
    -webkit-box-shadow: 0px 2px 0px #A6A6A6;
    -o-box-shadow: 0px 2px 0px #A6A6A6;
    -ms-box-shadow: 0px 2px 0px #A6A6A6;
    box-shadow: 0px 2px 0px #A6A6A6;
    padding: 10px 15px 7px 15px;
    box-sizing: border-box;
    margin: 0 0 20px 0px;
}
.inputField table tbody th {
	text-align:left;
	vertical-align:middle !important;
}
</style>
</head>
<body onLoad="f_ChangeInputColor()">
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

      <div class="main">
        <div class="wrapper">

  <!-- ##### /HEADER ##### -->
  		<!-- ##### /RAKUTEN ##### -->
		<?php
		        if($_SESSION['currentasid'] == ASID_RAKUTEN) {
			echo "<div style=\"border:2px solid orange; padding:5px;\"><a href=".LINK_RAKUTEN."><img src=\"/common/img/raketenlogo.gif\" Align=\"left\" Hspace=\"10\" Vspace=\"0\"></a>こちらのページからは、ポケカルのサイトとなります。&nbsp;<Input type=\"button\" value=\"楽天トラベルへ戻る\" style=\"WIDTH: 200px; HEIGHT: 20px; color: red; position: absolute; top: 120px; left: 600px;\" onClick=\"location.href='".LINK_RAKUTEN."'\"><br/>ご予約は「ポケカル」にて行われます。楽天スーパーポイントの対象外となりますので、あらかじめご了承ください。<br /><br /></div>";
		}
		?>
  <!-- ##### BODY ##### -->


		<div class="mb10"></div>
			
		<h1 class="headline01"><p>
			<?php 
			if($isJCOM) {
			?>
				イベント・ツアー予約(抽選) 
			<?php 
			}else{
			 	print($transl->printL('ibentoyoyaku'));
			} 
			?>
		</p></h1>

		<div class="mb50"></div>			
        <section id="mainField">

			<ol class="cart_stepbar">
				<li><span>1</span><br>予約内容入力</li>
				<li><span>2</span><br>予約内容確認</li>
				<li><span>3</span><br>カード手続</li>
				<li class="visited"><span>4</span><br><div class="newPoint">カード番号</div></li>
				<li><span>5</span><br>カード情報確認</li>
				<li><span>6</span><br>予約完了</li>
			</ol>
			
			<div class="mb20"></div>
			
			<p class="p14">
				<?php
				if($isJCOM) {
				?>
					<p style="color:red">※本ツアーは多数の応募が想定されるため、ツアー予約後に抽選を行い、当選した方のみご参加頂けます。</p>
					<p style="color:red">※抽選結果は、後日メールにて全申込者にご連絡いたします。</p>
				<?php
				}
				?>
				<ul class="alert">
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
				</ul>
			</p>
			
			<div class="mb50"></div>
			
			<form name="frm" method="post" action="<?php print($url_currentdir.$filename_current); ?>" id="<?php print($screenid_current); ?>">
				<input type="hidden" name="screenid" value="<?php print($screenid_current); ?>" />
				
				<h2 class="headline02"><p>
					お支払い金額
				</p></h2>

				<div class="mb10"></div>  

				<div class="panel">
					<div class="carttable">

						<table>
							<tr>
								<th>
									<?=$transl->printL('shiharaikingaku')?>
								</th>
								<td>
									<?php print($totalfee); ?><?=$transl->printL('yen')?>
								</td>
							</tr>
							<tr class="last">
								<th>
									<?=$transl->printL('shiharaihoho')?>
								</th>
								<td>
									<?=$transl->printL('kurezittoka-do')?>&nbsp;&nbsp; <span class="p14">※お支払い回数は、1回払のみとなります</span>
								</td>
							</tr>

						</table>
					</div>
				</div>
				
				<h2 class="headline02"><p>
					<?=$transl->printL('ka-dojohonyuryoku')?>
				</p></h2>

				<div class="mb10"></div>  

				<div class="panel">
					<div class="carttable">

						<table>
							<tr>
								<th>
									<?=$transl->printL('ka-dobango')?>
									<span class="require"><?=$hissu?></span>
								</th>
								<td>
									<input class="p16" type="text" name="cardno1" id="cardno1" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onchange="zenhan(this)" style="ime-mode: active"
									<?php
									if (array_key_exists('cardno1', $_POST)) {
										print('value="'.htmlspecialchars($_POST['cardno1'], ENT_QUOTES).'" ');
									}
									?>
									size="6" />ー
									<input class="p16" type="text" name="cardno2" id="cardno2" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onchange="zenhan(this)" style="ime-mode: active"
									<?php
									if (array_key_exists('cardno2', $_POST)) {
										print('value="'.htmlspecialchars($_POST['cardno2'], ENT_QUOTES).'" ');
									}
									?>
									size="6" />ー
									<input class="p16" type="text" name="cardno3" id="cardno3" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onchange="zenhan(this)" style="ime-mode: active"
									<?php
									if (array_key_exists('cardno3', $_POST)) {
										print('value="'.htmlspecialchars($_POST['cardno3'], ENT_QUOTES).'" ');
									}
									?>
									size="6" />ー
									<input class="p16" type="text" name="cardno4" id="cardno4" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onchange="zenhan(this)" style="ime-mode: active"
									<?php
									if (array_key_exists('cardno4', $_POST)) {
										print('value="'.htmlspecialchars($_POST['cardno4'], ENT_QUOTES).'" ');
									}
									?>
									size="6" /><br />
									
									<div class="mb5"></div>
									
									<div class="p16">
										<?=$transl->printL('ka-dobangochui')?>
									</div>
									
									<div class="mb10"></div>
									
									<img src="/images/book/cardbrands.gif" height="35px" />

									<div class="mb10"></div>
									
									<div class="p14">
										<span style="color: #FF0000">※AMEXカードをご利用の場合、下記のとおり、4桁-4桁-4桁-3桁でご入力ください。</span><br>
										（例）AMEXカードの入力例・・【1234】-【5678】-【9123】-【456】<br>
										<div class="mb10"></div>
										<span style="color: #FF0000">※ダイナースカードをご利用の場合、下記のとおり、 4桁-4桁-4桁-2桁でご入力ください。</span><br>
										（例）ダイナースカードの入力例・・【1234】-【5678】-【9123】-【45】
									</div>									
									
								</td>
							</tr>
							<tr class="last">
								<th>
									<?=$transl->printL('yukokigen')?>
									<span class="require"><?=$hissu?></span>
								</th>
								<td>
									<ul class="ul-form">
										<li class="clearfix">
											<div class="colKgn clearfix">
												<div class="select selectKgn">
													<select class="p16" type="select-one" name="cardmonth" id="cardmonth" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onchange="zenhan(this)">
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
												<p class="txtSlsh">
													／
												</p>
												<div class="select selectKgn">
													<select class="p16" type="select-one" name="cardyear" id="cardyear" onFocus="f_onFocus(this)" onBlur="f_onBlur(this)" onchange="zenhan(this)">
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
												<p class="txtCdYm">
													(MONTH/YEAR)
												</p>
											</div>
										</li>
									<ul>
									<div class="mb5"></div>
									<div class="p16">
										<?=$transl->printL('ka-doyukokigensetsumei')?>
									</div>
								</td>
							</tr>

						</table>
					</div>
				</div>	
				<div style="margin-bottom:80px;"></div>
	  			
				<p class="btn-detail"  onclick="return judge()"><a id="p1" onclick="document.frm.submit();">次へ</a></p>
                
			</form>
		</section>
			
		<div class="mb100"></div>	
			
        <div class="totop">
          <a href="#imgLogo" class="button">HOME</a>
        </div>
      </div>
    </div>
  </div>
  <!-- ##### /BODY ##### -->
  <!-- ##### TAG ##### -->
  <?php
    echo $tag_conv20121011;
  ?>
  <!-- ##### /TAG ##### -->
  <!-- ##### FOOTER #####  -->
      <?php include_once("include/2018/footer.html"); ?>

<script>
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