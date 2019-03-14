<?php
#ini_set( 'display_errors', 'On' );
error_reporting(E_ALL ^ E_WARNING);
if(strpos($_SERVER['HTTP_HOST'],'master') === false){
    if (empty($_SERVER['HTTPS'])) {
        header("HTTP/1.0 301 Moved Permanently");
        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        exit;
    }
}

require_once("include/2018/config.php");





$cms = new getDataFromCms();
$cms->setGuideListHtml($_GET['type']);
$data2 = $cms->res_data;

$text_type = "";
if($_REQUEST['type'] == 1){
	$text_type = "アンジーンガイド一覧";
}elseif($_REQUEST['type'] == 2){
	$text_type = "パートナーガイド一覧";
}else{
	$text_type = "ガイド一覧";
}
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<?php if( $lang == "english"){ ?>
<meta name="robots" content="noindex,nofollow" />
<?php } ?>
    <meta name="description" content="ポケカルが主催するツアーで、各ツアーのガイドを担当するメンバーのご案内。歴史や街・文化に精通したガイドのプロフェッショナルがご案内するツアーは、多くのお客様に大好評。ガイドを目当てにツアーを申し込むお客様もいらっしゃいます。当ページでは、ガイドのプロフィールやツアーにご参加いただいたお客様の声をご紹介。ぜひ、ツアー選びにお役立てください。">
    <meta name="keywords" content="ガイド,ポケカル,アンジーン">
    <title>ガイド一覧｜日帰りバス旅行・観光ツアーならポケカルへ</title>
    <link rel="stylesheet" href="/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="/common2/css/slick.css">
    <link rel="stylesheet" href="/common2/css/style.css?v=0713">

    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("/_data/tags/head_tag.php"); ?>
    <link rel="alternate" media="only screen and (max-width: 640px)" href="http://www.poke.co.jp/sp/guide-member/">

<style>
.guide_wrap{
	position:relative;
	overflow:hidden;
	margin:20px auto;
	padding-bottom:20px;
	border-bottom:1px solid #DDD;
}
.guide_l{
	width:150px;
	float:left;
	text-align:center;
	margin-left:20px;
}
.guide_r{
	width:820px;
	float:right;
}
.guide_prof{
	width:150px;
	border-radius:400px;
	overflow:hidden;
}
.guide_prof img{
	width:150px;
}
.guide_name{
	font-size:22px;
	margin-bottom:10px;
}
.guide_eval{
	margin-bottom:10px;
	font-size:14px;
}
.guide_intro{
	background:#EEE;
	padding:9px;
	font-size:16px;
	line-height:1.6;
}
.guide_separator{
	border-bottom:1px solid #DDD;
	height:1px;
}



.rate {
  position: relative;
  display: inline-block;
  width: 95px;
  height: 16px;
  font-size: 16px;
}
.rate:before, .rate:after {
  position: absolute;
  top: 0;
  left: 0;
  content: '★★★★★';
  display: inline-block;
  height: 16px;
  line-height: 16px;
}
.rate:before {
  color: #ddd;
}
.rate:after {
  color: #ffa500;
  overflow: hidden;
  white-space: nowrap;
}

.rate00:after {
  width: 0;
}

.rate10:after {
  width: 16px;
}

.rate15:after {
  width: 24px;
}

.rate20:after {
  width: 32px;
}

.rate25:after {
  width: 40px;
}

.rate30:after {
  width: 48px;
}

.rate35:after {
  width: 56px;
}

.rate40:after {
  width: 64px;
}

.rate45:after {
  width: 72px;
}

.rate50:after {
  width: 80px;
}

.wrap {
  width: 80px;
  margin: 0 auto;
}
.guide_lead{
	font-size:16px;
	margin:20px;
	color:#333;
	line-height:1.5;
	
	box-sizing:border-box;
}
</style>

<!-- User Heat Tag -->
<script type="text/javascript">
(function(add, cla){window['UserHeatTag']=cla;window[cla]=window[cla]||function(){(window[cla].q=window[cla].q||[]).push(arguments)},window[cla].l=1*new Date();var ul=document.createElement('script');var tag = document.getElementsByTagName('script')[0];ul.async=1;ul.src=add;tag.parentNode.insertBefore(ul,tag);})('//uh.nakanohito.jp/uhj2/uh.js', '_uhtracker');_uhtracker({id:'uhjD1c8Tz4'});
</script>
<!-- End User Heat Tag -->
  </head>
  <body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

      <div class="wrapper">
        <ul class="breadcrumb">
          <li><a class="trans" href="../">日帰り旅行・ツアーTOP</a></li>
          <li><?php echo $text_type; ?></li>
        </ul>

<div class="mb10"></div>

<h1 class="headline01"><p><?php echo $text_type; ?></p></h1>

<div class="mb20"></div>

<div class="guide_lead">ポケカルが主催するツアーで、各ツアーのガイドを担当するメンバーのご案内※。歴史や街・文化に精通したガイドのプロフェッショナルがご案内するツアーは、多くのお客様に大好評。ガイドを目当てにツアーを申し込むお客様もいらっしゃいます。当ページでは、ガイドのプロフィールや担当ツアーのスケジュールの他、ツアーにご参加いただいたお客様の声もご紹介。ぜひ、ツアー選びの中で、ガイドの評価も参考ください。※ポケカルのツアーを担当する一部のガイドメンバーの紹介となります。全てのメンバーではありません。ご了承の上、ページをご覧ください。
</div>
              <div class="mb30"></div>
          
          
<div class="guide_separator"></div>
          
<?php
echo $data2['guide_list'];
?>

<div class="mb100"></div>


        </div>
      </div>
      <?php include_once("include/2018/footer.html"); ?>