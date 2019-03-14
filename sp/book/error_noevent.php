<?php
require_once("include/2018/config.php");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<title>ページエラー｜ポケカル</title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <link rel="stylesheet" href="/sp/common/css/page.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
    <?php include_once("/_data/tags/head_tag.php"); ?>
<style>
.apply_btn {
	-webkit-appearance:none;
    display: block;

    margin: 0px auto 70px auto;
    text-decoration: none;
	border:none;
	width:220px;
    
    
    color: #FFF !important;
  
    text-align: center;
    
	
	background: #ec7391;
	
	border-radius: 0px;
    -webkit-border-radius: 0px;
    -moz-border-radius: 0px;
	
	padding:0.22rem 0.1rem 0.2rem;
	font-size:0.32rem;　

}
.error_msg{
	text-align:center;
	padding:50px 22px;
	font-size:0.32rem;
}
</style>
</head>
  <body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

      <div class="main">

        <div class="error_msg">
        ご指定いただいたツアー・イベント情報はただいま開催日がございません。

		<div class="mb20"></div>
        
        開催日が設定されているツアーは、<a href="/sp/all-themes/">テーマ一覧</a>や、<a href="/sp/all-features/">特集一覧</a>より検索することができます。再度お試しください。


        </div>
        
        <a href="/" class="apply_btn">TOPページへ戻る</a>
        

	</div><!-- /#top -->

</div>

<?php require_once("2018/footer.html"); ?>