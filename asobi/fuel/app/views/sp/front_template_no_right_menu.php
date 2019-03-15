<?php
#$domain_mypage = "mypage.poke.co.jp";
$domain_mypage = MYPAGE_HOST;
if($controller == "detail" && isset($data['tdk_description']) && $data['tdk_keywords'] && $data['tdk_title'] && $data['tdk_h1']):
$tdk_description = $data['tdk_description'];
$tdk_keywords = $data['tdk_keywords'];
$tdk_title = $data['tdk_title'];
$tdk_h1 = $data['tdk_h1'];
else:
$tdk_description = "バスツアーやクルーズ、体験イベントなどの日帰り旅行を予約するならポケカルへ。東京観光、女子旅、添乗員なしの気軽な一人旅プランなどなんでもお任せください！";
$tdk_keywords = "日帰りバスツアー,日帰り,バスツアー,旅行,日帰りツアー,ツアー,クルーズ,女子旅,東京観光,ポケカル";
$tdk_title = "日帰りバスツアーや旅行の予約ならポケカルへ";
$tdk_h1 = "日帰り旅行やバスツアーの予約ならポケカルへ";
endif;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-script-Type" content="text/javascript" />
<meta http-equiv="content-style-Type" content="text/css" />
<meta name="description" content="<?php echo $tdk_description; ?>" />
<meta name="keywords" content="<?php echo $tdk_keywords; ?>" />
<meta name="_globalsign-domain-verification" content="uF3MRzfyoKNYrpQ1mReq8_tpTHOItX5gmpd6CMFcfT" />
<title><?php echo $tdk_title ?></title>
<link rel="apple-touch-icon" href="./webclip.png">
<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0" />
<meta name="format-detection" content="telephone=no" />


<link rel="stylesheet" href="/sp/common/css/import_renew.css" />

<link rel="stylesheet" href="/asobi/css/sp-style.css" />



<!-- jQuery library (served from Google) -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/asobi/css/style_sp.css" />

    <?php include_once("tags/head_tag.php"); ?>
</head>

<body>
<?php include_once("tags/body_tag.php"); ?>
<a name="page"></a>
<h1 style="font-weight:normal;margin-bottom:3px;margin:6px 8px 0 8px;font-size:6px;">
日帰り旅行やバスツアーの予約ならポケカルへ
</h1>
<div class="header_renew">


<div style="margin-bottom:3px;"></div>

<div class="header_left">
<a href="/sp/"><img src="http://img.poke.co.jp/images/top/logo_170101.png" width="140" alt="ポケカル" style="margin-top:3px;margin-left:8px;"></a>
</div>

<div class="header_right">

<div align="right" style="margin-right:8px;">
<iframe width="150" height="58" src="//mypage.poke.co.jp/_checklogin.php?redirect=<?php echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>" frameborder="0" scrolling="no" id="ckecklogin"></iframe>
</div>


<div class="clear"></div>
<div id="header_sitemap"><a href="/sp/sitemap.html">サイトマップ&nbsp;></a></div>

</div>

</div><!-- header_renew -->

<?php /*
<div id="wrap_navtour">
<ul id="navtour">
<li><a href="<?php echo HTTP_HOST; ?>/" class="nav1up">バスツアー・イベント</a></li>
<li><a href="<?php echo HTTP_HOST; ?>/asobi/" class="nav1upcurrent">個人型 遊び･体験プラン</a></li>
</ul>
</div>
*/ ?>


<div id="wrap">

<?php echo $content; ?>


</div>


<p class="t-a-c">
<a href="#page" class="backtotop" style="color:#553820;text-decoration:none;"><img src="http://img.poke.co.jp/sp/images/top/icon_arrow_up.png" width="18">&nbsp;ページトップへ</a>
</p>

<br><br>


<br><br>
</div>





<footer>

<p class="menu">
<a href="/sp/howto/">お申込み方法</a>｜<a href="/sp/faq/">よくある質問</a><br>
<a href="/sp/company.html">運営会社</a>｜<a href="/sp/ryokogyo.html">旅行業登録票</a>｜<a href="/sp/privacy.html">個人情報保護</a><br>
  <a href="/sp/copyright.html">著作権</a>｜<a href="/sp/yakkan.html">約款</a>｜<a href="/sp/rule-travel/">旅行条件書</a>｜<a href="/sp/buslist.html">利用バス会社</a><br>
  <a href="/sp/tuuhan.html">通販規約</a>｜<a href="/sp/book/inquiry.php">お問い合わせ</a></p>

<div class="m-t-10"></div>


<p>
<small>Copyright &copy; Pokekaru-Culb. All rights reserved.</small>
</p>
</footer>



</body>
</html>