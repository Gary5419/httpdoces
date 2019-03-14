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



$lang = strtolower(getLangFromURL($_SERVER['PHP_SELF']));
if(isset($_GET['testview'])){
	#echo "GET\n";
	#print_r($_GET);
	#echo "params\n";
	#print_r($params);
	#exit;
	
	#require_once 'include/template.inc.php';
	
	
	if( $lang == "english"){
      echo "英語";
    }else{
      echo "日本語";
    }
	
}

$cms = new getDataFromCms();
$cms->guide_voice_paging = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$cms->guide_profile_baseurl = "/guide-member/profile/?guide_id=".$_REQUEST['guide_id'];
$cms->setGuideHtml($_GET['guide_id']);
$data2 = $cms->res_data;

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
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title><?php echo $data2['guide_meta']['title']; ?></title>
    <link rel="stylesheet" href="/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="/common2/css/slick.css">
    <link rel="stylesheet" href="/common2/css/style.css?v=0713">
    <link rel='stylesheet' href='/tokyo-cruise/css/style.css'>

    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("/_data/tags/head_tag.php"); ?>
<style>
.guide_head{
	position:relative;
	z-index:1;
	height:500px;
	position:relative;
	overflow:hidden;
	margin-bottom:10px;
}
.guide_cover{
	position:absolute;
	top:0;
	left:0;
}
.guide_profile{
	position:absolute;
	z-index:1000;
	top:20px;
	left:20px;
	background:#FFF;
	padding:10px;
	width:170px;
	text-align:center;
}

.guide_voice_wrap{
	position:relative;
	overflow:hidden;
	margin:20px auto;
/*	padding-bottom:20px;*/
/*	border-bottom:1px solid #DDD;*/
}
.guide_voice_l{
	width:100px;
	float:left;
	text-align:center;
	margin-left:20px;
	font-size:12px;
}
.guide_voice_r{
	width:870px;
	float:right;
	margin-right:20px;
}

.guide_photo{
	width:150px;
	border-radius:400px;
	overflow:hidden;
	margin-bottom:10px;
}
.guide_photo img{
	width:150px;
}
.guide_voice_name{
	font-size:22px;
	margin-bottom:10px;
}
.guide_voice_eval{
	margin-bottom:15px;
	font-size:14px;
}
.guide_voice_intro{
	background:#EEE;
	padding:9px 20px;
	font-size:16px;
	line-height:1.8;
}
.guide_voice{
	margin-bottom:10px;
}
.guide_separator{
	border-bottom:1px solid #DDD;
	height:1px;
}

.guide_main_title{
	font-size:16px;
	color:#ec7391;
	font-weight:bold;
	text-align:center;
	width:100%;
	padding:7px 15px 7px 15px;
}
.guide_main_img{
	height:234px;
	overflow:hidden;
}
.guide_eventname{
	font-size:16px;
	font-weight:bold;
	padding:10px 0;
}
.guide_price{
	text-align:center;
	font-size:16px;
	padding-bottom:20px;
}
.guide_msg{
	font-size:14px;	
	
}
.guide_voicecount{
	font-size:18px;
	font-weight:bold;
	border-top:1px solid #DDD;
	border-bottom:1px solid #DDD;
	padding:7px 20px;
	width:100%;
	box-sizing:border-box;
}
.guide_baloon{
	background:#FFFFD1;
	padding:10px 20px;
	width:100%;
	box-sizing:border-box;
	line-height:1.6;
}
.guide_baloon b{
	font-weight:bold !important;
}
.guide_voice_intro2 table td{
	padding:5px;
	font-size:16px;
}
.guide_lead{
	font-size:16px;
	margin:20px;
	color:#333;
	line-height:1.5;
	
	box-sizing:border-box;
}
.guide_novoice{
	font-size:16px;
	margin:30px 60px 200px 60px;
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



ul#ticket_main li{
	height:480px;
}

</style>
  </head>
  <body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

          
<?php
echo $data2['guide']['profile'];
?>




        </div>
      </div>
      <?php include_once("include/2018/footer.html"); ?>