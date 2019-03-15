<?php
require_once("include/2018/config.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="Description" content="" />
<meta name="Keywords" content="" />
<title>ページエラー｜ポケカル</title>
    <link rel="stylesheet" href="/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="/common2/css/slick.css">
    <link rel="stylesheet" href="/common2/css/style.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->

<style>
a.apply_btn {
	-webkit-appearance:none;
    display: block;
	width:260px;
	height:48px;
    
    margin: 0px auto 100px auto;
    text-decoration: none;
    color: #FFF !important;
    font-size: 18px;
    text-align: center;
	
	background: #ec7391;
	width:240px;
	line-height:48px;
}
.error_msg{
	text-align:center;padding:150px 0 ;font-family:Meiryo,"メイリオ",  "ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", Osaka, "ＭＳ Ｐゴシック", "MS PGothic", sans-serif;
	font-size:18px;
	line-height:1.7;
}
</style>
    <?php include_once("tags/head_tag.php"); ?>
</head>
 <body>
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html" ); ?>

      <div class="main">
        <div class="wrapper">

        <div class="error_msg">

ご指定いただいたツアー・イベント情報はただいま開催日がございません。<br>
開催日が設定されているツアーは、<a href="/all-themes/">テーマ一覧</a>や、<a href="/all-features/">特集一覧</a>より<br>
検索することができます。再度お試しください。


        
        </div>
        
        
        <a href="/" class="apply_btn">TOPページへ戻る</a>
        
        
      </div>
    </div>
  </div>

<div class="mb40"></div>
      <?php include_once("include/2018/footer.html" ); ?>