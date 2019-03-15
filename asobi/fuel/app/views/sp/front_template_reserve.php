<?php
#$domain_mypage = "mypage.poke.co.jp";
$domain_mypage = MYPAGE_HOST;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-script-Type" content="text/javascript" />
<meta http-equiv="content-style-Type" content="text/css" />
<meta name="description" content="大人の遊び・体験予約では、大人が楽しめる遊びや施設情報をご紹介。仕事帰り・休日に行ける少し贅沢で非日常なおでかけ体験。デートやご夫婦・ご家族でご利用ください。" />
<meta name="keywords" content="大人,遊び,休日,ポケカル" />
<title>大人の遊び・体験予約 | ポケカル</title>
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
<!-- <div id="page02"> -->
<div>


<!-- <div id="page02"> -->
<a name="page"></a>
<h1 style="font-weight:normal;margin-bottom:3px;margin:6px 8px 0 8px;font-size:6px;">
大人の遊び・体験予約
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

<div id="wrap">

<?php echo $content; ?>


</div>

<div class="mb100"></div>

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