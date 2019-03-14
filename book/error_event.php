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
<meta name="copyright" content="" />
<title>エラーページ｜ポケカル</title>
    <link rel="stylesheet" href="/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="/common2/css/slick.css">
    <link rel="stylesheet" href="/common2/css/style.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
    <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
 <body>
 <?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html" ); ?>

      <div class="main">
        <div class="wrapper">

<style>
#errmsg{
	text-align:center;font-size:14px;line-height:2;font-family:Meiryo,"メイリオ",  "ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", Osaka, "ＭＳ Ｐゴシック", "MS PGothic", sans-serif;padding:200px 0;/*margin:30px auto 50px auto;*/
}
</style>
<div id="errmsg">
入力された商品番号に該当するツアー・プランがありませんでした。<br>
もう一度、商品番号をご確認の上、検索ください。
</div>

</div>
</div>
</div>

        <div class="mb40"></div>
      <?php include_once("include/2018/footer.html" ); ?>


