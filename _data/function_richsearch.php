<?php
#ini_set("display_errors",1);
require_once("include/2018/config.php");	#リニューアル時追加


if(strpos($_SERVER['HTTP_HOST'],'master') === false){
    if (empty($_SERVER['HTTPS'])) {
        header("HTTP/1.0 301 Moved Permanently");
        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        exit;
    }
}

#ini_set("display_errors",1);
$special_title = $master[0];
#$column_pc = $master[1];
#$column_sp = $master[2];
$column_pc = 1;
$column_sp = 1;
$update_flag = $master[3];
$url = $master[4];
$caption = $master[5];
$breadcrumb = $master[6];
define("H1TXT",$master[7]);
$title = $master[8];
$description = $master[9];
$keywords = $master[10];
$h2 = $master[11];
$h2color = strtolower($master[12]);
$h2lead = $master[13];
$image = $master[14];
$word = rawurlencode($master[15]);
$purposeid = $master[16];
$btn_name = $master[17] == 1 ? "ツアー・イベントの詳細を見る" : "チケットの詳細を見る";

if(isset($master[18]))	$alternate = $master[18] ; else $alternate = "" ;
if(isset($master[19]))	$canonicalpc = $master[19] ; else $canonicalpc = "" ;
if(isset($master[20]))	$canonicalsp = $master[20] ; else $canonicalsp = "" ;

#ASIDパラメータ
if(isset($_REQUEST['asid'])){
	$prm_asid = "&asid=".$_REQUEST['asid'];
}else{
	$prm_asid = "";
}

switch($column_pc){
	case 1:
		$offset_title_pc = 300;
		$offset_caption_pc = 300;
		$offset_summary_pc = 209;
		break;
	case 2:
		$offset_title_pc = 70;
		$offset_caption_pc = 27;
		$offset_summary_pc = 156;
		break;
	default:
		$offset_title_pc = 300;
		$offset_caption_pc = 300;
		$offset_summary_pc = 209;
		break;	
}



#$params["keyword"] = $word;
#$params["purposeid"] = $purposeid;
$params["purpose_id"] = $purposeid;
#$params["purpose_id"] = "web-limited-tours";

if( isset($_GET['page']) ) $params['page'] = $_GET['page'];
if( isset($_GET['sort']) ) $params['sort'] = $_GET['sort'];

if($params["purpose_id"] != ""){
	$list = getSearchData2($params);
}else{
	$list = array();
}


$m_area = $cms->getAreamaster();
$m_theme = $cms->getThememaster();
/*
$params = array(
'stock_date_from' => '',
'stock_date_to' => '',
'adults_cnt' => '',
'children_cnt' => '',
'adult_fee_from' => '',
'adult_fee_to' => '',
'keyword' => '',
'tour_code' => '',
'theme_id' => '',
'area_id' => '',
'purpose_id' => '',
);
*/
/*
foreach($_GET as $k => $v){
	if($k == "theme_id" || $k == "area_id" || $k == "pref_id" || $k == "purpose_id"):
	#if($k == "theme_id"):
		$params[$k] = implode(",",$v);
	#elseif($k == "pref_id"):
		#不要なのでスルー
	else:
		$params[$k] = $v;
	endif;
}

if( isset($_GET['pref_id']) ){
	$params['area_id'] = "";
	$cnt = 1;
	foreach($_GET['pref_id'] as $pref_id){
		$params['area_id'] .= implode(",",$config_area_convert[$pref_id]) ;
		if(count($_GET['pref_id']) > $cnt){
			$params['area_id'] .= ",";
		}
		$cnt++;
	}
}
*/
$lang = strtolower(getLangFromURL($_SERVER['PHP_SELF']));


$paramurl_arr = array();

foreach($_GET as $k => $v){
	if($k == "page")	continue;
	if($k == "sort")	continue;
	if($k == "theme_id" || $k == "area_id" || $k == "pref_id" || $k == "purpose_id"):
		foreach($v as $v2):
			$paramurl_arr[] = $k."[]=".$v2;
		endforeach;
	else:
		$paramurl_arr[] = $k."=".$v;
	endif;
}
$search_base_url = $_SERVER['SCRIPT_NAME']."?".implode("&",$paramurl_arr);
#echo $search_base_url;exit;

#10月問題対策
$params['stock_date_from'] = str_replace("-010-","-10-",$params['stock_date_from']);
$params['stock_date_to'] = str_replace("-010-","-10-",$params['stock_date_to']);




$res_data = $cms->getSearchData($params);
if(isset($_REQUEST['testview1'])){
	print_r($params);
	print_r($res_data);
}

#exit;








































function getSearchData2($params){

		$m_params = array(
		'stock_date_from' => '',
		'stock_date_to' => '',
		'adults_cnt' => '',
		'children_cnt' => '',
		'adult_fee_from' => '',
		'adult_fee_to' => '',
		'keyword' => '',
		'tour_code' => '',
		'theme_id' => '',
		'area_id' => '',
		'purpose_id' => '',
		);
		if(!isset($params['page']))	$params['page'] = 1;
		
		$search_params = array();
		foreach($params as $k => $v){
			if( !array_key_exists($k , $m_params) ) continue;
			if($v != "")	$search_params[$k] = $v;
		}
		#print_r($params);
		#print_r($search_params);
		#exit;
		$header = array("Content-type: application/json; charset=UTF-8",
			"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		$options = array('http' =>
			array(
				'method' => 'GET',
				'header'  => implode("\r\n", $header), 
			)
		);
		$data =http_build_query($search_params);
		
		$url = "http://pokekaru-webapi.azurewebsites.net" . "/api/search/events/";
		
		if(isset($_GET['testview'])){
			echo $url."?".$data."<br>\n";
		}
		#exit;
		#echo $url . '?' . $data;
		$res = file_get_contents($url . '?' . $data, false, stream_context_create($options));
		$res = mb_convert_encoding($res, "UTF-8", "auto");
		
		$res_data = json_decode($res,true);
		
		#ソート変更：更新順
		if(isset($_GET['sort']) && $_GET['sort'] == "new"){
			$events = $res_data['events'];
			foreach($res_data['events'] as $key => $val){
				$modified[$key] = $val["modified"];
			}
			array_multisort($modified, SORT_DESC, $events);
			$res_data['events'] = $events;
		}
	
		#print_r($res_data);
		$list = array();
		foreach($res_data['events'] as $v){
			$eventid = $v['event_code'];
			$list[$eventid]['event_id'] = $eventid;
			$list[$eventid]['title'] = $v['event_name'];
			$summary = mb_substr(strip_tags($v['web_event_contents']), 0, 250, "UTF-8");;
			$summaryArr = explode("&nbsp;",$summary);
			$list[$eventid]['summary_caption'] = $summaryArr[0];
			$list[$eventid]['summary'] = $summaryArr[1];
			$list[$eventid]['link'] = "/book/calendar.php?eventid=".$eventid;;
			$list[$eventid]['alt'] = $v['event_name'];
			$list[$eventid]['img'] = MAP_URL.$v['picture1'];
			$list[$eventid]['img2'] = $v['picture2'] != "" ? MAP_URL.$v['picture2'] :"";
			$list[$eventid]['img3'] = $v['picture3'] != "" ? MAP_URL.$v['picture3'] : "";
			$list[$eventid]['term'] = date("Y年m月d日",strtotime($v['stock_date_from'])) . " 〜 " . date("Y年m月d日",strtotime($v['stock_date_to'])) ;
			$list[$eventid]['area'] = implode("<br />",$v['areas']);
			$list[$eventid]['price'] = "<em>".number_format($v['adult_fee'])."円</em>（".$v['abstract']."）";
			$list[$eventid]['api_syugouplace'] = $v['meeting_place'];
		}
		
		return $list;
}


###################
#	PC
###################
if(!isset($mode)):
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="description" content="<?php echo $description;?>" />
<meta name="keywords" content="<?php echo $keywords;?>" />
<title><?php echo $title;?></title>

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

<?php /*
<script type="text/javascript">
$(document).ready(function() {
	var pokeLoginDomain = "//mypage.poke.co.jp";
	var loginChkUrl = "https://mypage.poke.co.jp/_checklogin.php";

	var targetOrigin = loginChkUrl ;
	$('<iframe />', {
		src:     targetOrigin,
		id:      'pokeLoginFrame',
		name: 'pokeLoginFrame',
		frameborder: 0,
		style:  'display: none;',
		load: function() {

		$(window).on("message", function (event) {

		var cmpOrigin = event.originalEvent.origin.replace( /http:/g , "" ).replace(/https:/g , "") ;
		if (cmpOrigin == pokeLoginDomain) {
			
			var authCode = event.originalEvent.data;
			
			
			var dt = JSON.parse(event.originalEvent.data);
			var login_flag = dt.login_flag;
			if(login_flag == 1){
				var member_id = dt.mpid;
				var session_id = dt.mpsess;
				
				$('[name=member_id]').val(member_id);
				$('[name=session_id]').val(session_id);
			
　　　　　　　　　 } else {
				
				return;
			}
		}else{
			
		}
	});

	pokeLoginFrame.postMessage('', targetOrigin);
}				 
}).appendTo('body');

//GETパラメータ取得
var atid = $(document).getUrlParam("atid");
var asid = $(document).getUrlParam("asid");
var asmgtid = $(document).getUrlParam("anyid");
  
  var today_date = new Date();
  var yy = String(today_date.getFullYear());
  var mm = String(today_date.getMonth() + 1);
  var dd = String(today_date.getDate());
  var h = String(today_date.getHours());
  var m = String(today_date.getMinutes());
  var s = String(today_date.getSeconds());
  if (mm < 10)
	mm = "0" + mm;
  if (dd < 10)
	dd = "0" + dd;
  if (h < 10)
	h = "0" + h;
  if (m < 10)
	m = "0" + m;
  if (s < 10)
	s = "0" + s;
  var visitday = yy + mm + dd;
  var visitdatetime = yy + "/" + mm + "/" + dd + " " + h + ":" + m + ":" + s;
  //Cookieの有効期間チェック
  if ($.cookie('pokecurrent') != null) {
	var poke_current = $.cookie('pokecurrent').split(",");
	var cookie_visitdatetime = Date.parse(poke_current[2]);
	var cookie_time = new Date();
	cookie_time.setTime(cookie_visitdatetime);
	var diff = (today_date - cookie_time) / 1000; //秒
	//1時間を越えていたらCookie削除
	if (diff > 3600) {
	  $.cookie('pokecurrent', null);
	}
  }
  //asidをcookieに格納
  if (asid != null) {
	currentdata = new Array(atid, asid, visitdatetime, asmgtid);
	$.cookie('pokecurrent', currentdata);
	//初回訪問日をCookieに格納
	var firstvisitasid = $.cookie('pokekaru');
	if (firstvisitasid == null) {
	  firstvisitdata = new Array(atid, asid, visitday);
	  $.cookie('pokekaru', firstvisitdata, {expires: 180}); //180日間有効
	}
  }
 
});
</script>
<?php
*/ 

if(isset($canonicalpc) && $canonicalpc != ""):
	echo $canonicalpc."\n";
endif;
if(isset($alternate) && $alternate != ""):
	echo $alternate."\n";
endif;
?>
<style>
.search-body{
	border-bottom:none;
}
</style>

<!-- User Heat Tag -->
<script type="text/javascript">
(function(add, cla){window['UserHeatTag']=cla;window[cla]=window[cla]||function(){(window[cla].q=window[cla].q||[]).push(arguments)},window[cla].l=1*new Date();var ul=document.createElement('script');var tag = document.getElementsByTagName('script')[0];ul.async=1;ul.src=add;tag.parentNode.insertBefore(ul,tag);})('//uh.nakanohito.jp/uhj2/uh.js', '_uhtracker');_uhtracker({id:'uhjD1c8Tz4'});
</script>
<!-- End User Heat Tag -->
</head>
<body>
    <?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

<div class="main">
<div class="wrapper">
<!-- ##### BODY ##### -->


     
    <!-- +##### CONTENTS ##### -->
        
            
 
  <!-- ##### BREADCRUMBS ##### -->
  <ul class="breadcrumb">
            <li><?php echo $breadcrumb;?></li>
  </ul>
  <!-- ##### /BREADCRUMBS ##### -->
      <!-- ##### MAIN ##### -->
<div id="special<?php echo $column_pc;?>">


  <div class="keyvisual"><img src="/images/special/<?php echo $image;?>" width="100%" alt="Main Visual"></div>

<div class="geihinkan01">
<?php if($special_title != ""): ?>
	<h1 class="sec-ttl"><?php echo H1TXT; #$special_title; ?></h1>
<?php endif; ?>
<?php if($caption != ""): ?>
    <p class="desc"><?php echo $caption;?></p>
<?php endif; ?>
</div>

<div class="geihinkan02">
<?php if($h2 != ""): ?>
    <h2 class="ttl"><?php echo $h2;?></h2>
<?php endif; ?>
<?php if($h2lead != ""): ?>
    <p class="txt"><?php echo $h2lead;?></p>
<?php endif; ?>
</div>


<?php /* <div class="mb20"></div> */ ?>

          <div class="search-heading">


<?php #print_r($m_area); 

$sort = "";
if(isset($_GET['sort'])):
	$sort = "&sort=".$_GET['sort'];
endif;
?>

            <div class="pagination">
              <ul>
                <li class="prev">
<?php if($res_data['page'] > 1): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] - 1 ).$sort;?>">Prev</a>
<?php endif; ?>
                </li>

<?php for($i=1;$i<=$res_data['max_page']; $i++): ?>
<li<?php if($res_data['page'] == $i) echo ' class="active"'; ?>><a href="<?php echo $search_base_url."&page=".$i.$sort;?>"><?php echo $i; ?></a></li>
<?php endfor; ?>
                <li class="next">
<?php if($res_data['page'] < $res_data['max_page']): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] + 1).$sort;?>">Next</a>
<?php endif; ?>                
                </li>
              </ul>
            </div>



            <div class="group">
              <div class="order">

<?php if($res_data['count'] > 0): ?>

                <p class="title">[表示順]</p>
                <ul>
<?php
if(isset($_GET['sort']) && $_GET['sort'] == "new"):
?>
                  <li><a href="<?php echo $search_base_url; ?>">標準</a></li>
                  <li class="active"><a href="<?php echo $search_base_url."&sort=new";?>">新着順</a></li>
<?php
else:
?>
                  <li class="active"><a href="<?php echo $search_base_url; ?>">標準</a></li>
                  <li><a href="<?php echo $search_base_url."&sort=new";?>">新着順</a></li>
<?php
endif;
/*
                  <li><a href="#">人気順</a></li>
                  <li class="active"><a href="#">新着順</a></li>
                  <li><a href="#">価格の安い順</a></li>
                  <li><a href="#">価格の高い順</a></li>
                  <li><a href="#">おすすめ順</a></li>
                  <li><a href="#">口コミ数順</a></li>
*/ ?>
                </ul>

              </div>
              <div class="count-wrap">
                <p class="count"><?php if($res_data['first'] != "")	echo $res_data['count']."件中".$res_data['first']."-".$res_data['end']."件表示"; ?></p>
                <div class="info">
<?php /*
                  <p class="title">表示件数：</p>
                  <ul>
                    <li class="active"><a href="#">15件</a></li>
                    <li><a href="#">30件</a></li>
                    <li><a href="#">50件</a></li>
                  </ul>
*/ ?>
                </div>
              </div>

<?php endif; #ゼロヒットの時は見せない処理 ?>
            </div>

          </div><!-- search-heading -->
          <div class="search-body">

<?php if($res_data['count'] > 0): 

$image_base = BOOKING_URL_SCHEME . "://img.poke.co.jp/media/";
$calendar_base = "/book/calendar.php?eventid=";
foreach($res_data['events'] as $k => $row): ?>
            <div class="sr-item">
              <h3 class="headline03"><span><?php echo $row['event_name']; ?></span></h3>
              <div class="tours">
                <ul class="photo-list">
                <?php for($i = 1;$i<=3;$i++): 
				if( getimagesize($image_base . $row['picture' . $i]) ):
				?>
                <li><a class="trans" href="<?php echo $calendar_base.$row['event_code']; ?>"><img src="<?php echo $image_base . $row['picture' . $i]; ?>" alt="photo"></a></li>
                <?php endif; endfor;?>
                
                <?php /*
                  <li><a class="trans" href="<?php echo $calendar_base.$row['event_code']; ?>"><img src="<?php echo $image_base . $row['picture1']; ?>" alt="photo"></a></li>
                  <li><a class="trans" href="<?php echo $calendar_base.$row['event_code']; ?>"><img src="<?php echo $image_base . $row['picture2']; ?>" alt="photo"></a></li>
                  <li><a class="trans" href="<?php echo $calendar_base.$row['event_code']; ?>"><img src="<?php echo $image_base . $row['picture3']; ?>" alt="photo"></a></li>
				  */ ?>
                </ul>
                <p class="desc"><?php echo mb_substr(strip_tags($row['web_event_contents']), 0, 250, "UTF-8"); ?></p>
                <div class="panel">
                  <div class="panel-heading">
                    <div class="left">
<?php /*
                      <span class="orange">WEB予約限定プラン</span>
                      <span class="green">クレジットカード決済</span>
*/ ?>
                    </div>
                    <div class="right">
                    <?php /*
                      <a class="like" href="#"><i class="fa fa-heart"></i>行きたい！</a><span class="amount">XXX</span>
					  */ ?>
                      <a class="favorite01" href="#" onclick="setFavorite('<?php echo str_replace("P","",$row['event_code'])*1  ; ?>');return false;"><img src="/common2/img/common/icon_favorite.png" alt="favorite">お気に入りリストに追加</a>
                    </div>
                  </div>
                  <div class="panel-body">
                    <table>
                      <tr>
                        <th>テーマ</th>
                        <td class="col01 vrmiddle">
                        <?php
                        if( count($row['themes']) > 0):
                            foreach($row['themes'] as $themeid => $themename):
                        ?>
                        <a href="/book/eventlist.php?theme_id[]=<?php echo $themeid; ?>"><?php echo $themename; ?></a>　
                        <?php
                            endforeach;
                        endif;
                        ?>
                        </td>
                        <th>特集</th>
                        <td>
                        <?php
                        if( count($row['purposes']) > 0):
                            foreach($row['purposes'] as $purposeid => $purposename):
                        ?>
                        <a href="/<?php echo str_replace("_","/",$purposeid); ?>/"><?php echo $purposename; ?></a>　
                        <? /* <a href="/book/eventlist.php?purpose_id[]=<?php echo $purposeid; ?>"><?php echo $purposename; ?></a>　*/ ?>
                        <?php
                            endforeach;
                        endif;
                        ?>
                        </td>
                      </tr>
                      <tr>
                        <th>商品番号</th>
                        <td class="col01"><?php echo $row['event_code']; ?></td>
                        <th>口コミ</th>
                        <td class="rate01">
                        <?php /*
                        <span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><small>（<a href="#">XX件</a>）</small>
						*/ ?>
						</td>
                      </tr>
                      <tr>
                        <th>開催期間</th>
                        <td class="col01 vrmiddle"><?php echo $row['stock_date_from']."〜".$row['stock_date_to'];?><?php /*2016年05月15日 ～ 2018年03月31日*/ ?></td>
                        <th>エリア</th>
                        <td>
                        <?php
						if( count($row['areas']) > 0):
							foreach($row['areas'] as $areaid => $areaname):
						?>
						<a href="/book/eventlist.php?area_id[]=<?php echo $areaid; ?>"><?php echo $areaname; ?></a>　
						<?php
							endforeach;
						endif;
						?>
                        <?php /*神奈川：横浜 */ ?></td>
                      </tr>
                      <tr>
                        <th>料金</th>
                        <td colspan="3"><span class="c_dd0000"><?php echo number_format($row['adult_fee']); ?>円</span>～
                        <?php
                        if ($row['abstract'] == null) {
                          echo "&nbsp;";
                        } else {
                          echo htmlspecialchars("（" . $row['abstract'] . "）");
                        }
                        ?></td>
                      </tr>
                      <tr>
                        <th>集合場所</th>
                        <td colspan="3"><?php echo $row['meeting_place']; ?></td>
                      </tr>
                    </table>
                  </div>
                  <p class="btn-detail"><a href="<?php echo $calendar_base.$row['event_code']; ?>">詳細を見る</a></p>
                </div>
              </div>
            </div>
<?php endforeach; ?>

<?php else: ?>

<div class="no_result">
ご指定の検索条件に該当するツアー・イベントは見つかりませんでした。<br>
条件を変えて、再度検索ください。
</div>

<?php endif; ?>

          </div><!-- search-body -->






          <div class="search-bottom">
            <div class="group">
              <div class="order">
<?php /*
                <p class="title">表示順：</p>
                <ul>
                  <li><a href="#">人気順</a></li>
                  <li class="active"><a href="#">新着順</a></li>
                  <li><a href="#">価格の安い順</a></li>
                  <li><a href="#">価格の高い順</a></li>
                  <li><a href="#">おすすめ順</a></li>
                  <li><a href="#">口コミ数順</a></li>
                </ul>
*/ ?>
              </div>
              <div class="count-wrap">

<?php if($res_data['count'] > 0): ?>
                <p class="count"><?php if($res_data['first'] != "") echo $res_data['count']."件中".$res_data['first']."-".$res_data['end']."件表示"; ?></p>
<?php endif; ?>

                <div class="info">
<?php /*
                  <p class="title">表示件数：</p>
                  <ul>
                    <li class="active"><a href="#">15件</a></li>
                    <li><a href="#">30件</a></li>
                    <li><a href="#">50件</a></li>
                  </ul>
*/ ?>
                </div>
              </div>
            </div>
<?php /*            <!--#include virtual="../includes/pagination.html" --> */ ?>
          </div><!-- search-bottom -->
        </div><!-- / .search-results -->



            <div class="pagination">
              <ul>
                <li class="prev">
<?php if($res_data['page'] > 1): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] - 1 ).$sort;?>">Prev</a>
<?php endif; ?>
                </li>

<?php for($i=1;$i<=$res_data['max_page']; $i++): ?>
<li<?php if($res_data['page'] == $i) echo ' class="active"'; ?>><a href="<?php echo $search_base_url."&page=".$i.$sort;?>"><?php echo $i; ?></a></li>
<?php endfor; ?>
                <li class="next">
<?php if($res_data['page'] < $res_data['max_page']): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] + 1).$sort;?>">Next</a>
<?php endif; ?>                
                </li>
              </ul>
            </div>


</div><!-- search body -->

<div class="clearfix"></div>


<?php
if( count($list) == 0):
?>

<section>
  <div class="wrapper">
    <?php include_once("include/2018/feature.html"); ?>
  </div>
</section>
<?php
endif;
?>

<div class="mb40"></div>
                       
<?php /*
<div class="totop">
  <a href="#imgLogo" class="button">HOME</a>
</div>
*/ ?>

</div>

</div>
<?php include_once("include/2018/footer.html"); ?>
<?php 
###################
#	SP
###################
elseif($mode == "sp"): 
$breadcrumb = str_replace("</a> >","</a> /",$breadcrumb);
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="description" content="<?php echo $description;?>" />
<meta name="keywords" content="<?php echo $keywords;?>" />
<title><?php echo $title;?></title>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
<?php /*
<script type="text/javascript">
$(document).ready(function(){
	
    //GETパラメータ取得
    var atid = $(document).getUrlParam("atid");
    var asid = $(document).getUrlParam("asid");
    var today_date = new Date();
    var yy = String(today_date.getFullYear());
    var mm = String(today_date.getMonth() + 1);
    var dd = String(today_date.getDate());
    var h = String(today_date.getHours());
    var m = String(today_date.getMinutes());
    var s = String(today_date.getSeconds());
    if(mm < 10) mm = "0" + mm;
    if(dd < 10) dd = "0" + dd;
    if(h < 10) h = "0" + h;
    if(m < 10) m = "0" + m;
    if(s < 10) s = "0" + s;
    var visitday = yy + mm + dd;
    var visitdatetime = yy + "/" + mm + "/" + dd + " " + h + ":" + m + ":" + s;
    //Cookieの有効期間チェック
    if($.cookie('pokecurrent') != null){
	var poke_current = $.cookie('pokecurrent').split(",");
	var cookie_visitdatetime = Date.parse(poke_current[2]);
	var cookie_time = new Date();
	cookie_time.setTime(cookie_visitdatetime);
	var diff = (today_date - cookie_time) / 1000; //秒
	//1時間を越えていたらCookie削除
	if(diff > 3600){
	    $.cookie('pokecurrent', null);
	}
    }
    //asidをcookieに格納
    if (asid != null) {
	currentdata = new Array(atid, asid, visitdatetime);
	$.cookie('pokecurrent', currentdata);
	//初回訪問日をCookieに格納
	var firstvisitasid = $.cookie('pokekaru');
	if (firstvisitasid == null){
	    firstvisitdata = new Array(atid, asid, visitday);
	    $.cookie('pokekaru', firstvisitdata,{ expires: 180 }); //180日間有効
	}
    }
    
});
</script>
<?php
*/

if(isset($canonicalsp) && $canonicalsp != ""):
	echo $canonicalsp."\n";
endif;
?>

<!-- User Heat Tag -->
<script type="text/javascript">
(function(add, cla){window['UserHeatTag']=cla;window[cla]=window[cla]||function(){(window[cla].q=window[cla].q||[]).push(arguments)},window[cla].l=1*new Date();var ul=document.createElement('script');var tag = document.getElementsByTagName('script')[0];ul.async=1;ul.src=add;tag.parentNode.insertBefore(ul,tag);})('//uh.nakanohito.jp/uhj2/uh.js', '_uhtracker');_uhtracker({id:'uhjD1c8Tz4'});
</script>
<!-- End User Heat Tag -->
      <?php include_once("tags/head_tag.php"); ?>
</head>
  <body>
    <?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">

      <?php require_once("2018/header.html"); ?>


      <div class="main">
        <ul class="breadcrumb">
        <?php echo $breadcrumb;?>
        </ul>
        
<div id="special<?php echo $column_sp;?>">
     
    <!-- +##### CONTENTS ##### -->
        
            

      <!-- ##### MAIN ##### -->


<h1 class="keyvisual"><img src="/sp/images/special/<?php echo $image;?>" width="100%" /></h1>


        <div class="geihinkan01">
          <div class="wrapper">
<?php if($special_title != ""): ?>
          	<h2 class="sec-ttl"><?php echo H1TXT; #$special_title; ?></h2>
<?php endif; ?>
<?php if($caption != ""): ?>
            <p class="desc"><?php echo $caption;?></p>
<?php endif; ?>
          </div>
        </div>
        <div class="geihinkan02">
          <div class="wrapper">
<?php if($h2 != ""): ?>
            <h3 class="ttl"><?php echo $h2;?></h3>
<?php endif; ?>
<?php if($h2lead != ""): ?>
            <p class="txt"><?php echo $h2lead;?></p>
<?php endif; ?>
          </div>
        </div>
        
        <div class="search-results eventlist">
          <div class="search-heading">
<?php /*
            <div class="group">
              <ul class="clearfix">
                <li><span>新着順</span></li>
                <li><span>15件</span></li>
              </ul>
            </div>
*/ ?>


<?php 
$sort = "";
if(isset($_GET['sort'])):
	$sort = "&sort=".$_GET['sort'];
endif;

if($res_data['count'] > 0): ?>
            <div class="pagination">
              <ul>
              <li class="prev">
<?php if($res_data['page'] > 1): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] - 1 ).$sort;?>">Prev</a>
<?php endif; ?>
                </li>
<?php for($i=1;$i<=$res_data['max_page']; $i++): ?>
<li<?php if($res_data['page'] == $i) echo ' class="active"'; ?>><a href="<?php echo $search_base_url."&page=".$i.$sort;?>"><?php echo $i; ?></a></li>
<?php endfor; ?>
                <li class="next">
<?php if($res_data['page'] < $res_data['max_page']): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] + 1).$sort;?>">Next</a>
<?php endif; ?>   
                </li>
              </ul>
            </div>

<style>
.search_tab{
	border-bottom:1px solid #333;
	padding-left: 0.16rem;
	padding-right: 0.16rem;
	margin:30px auto 40px auto;
}
.search_tab ul{
	width:100%;
	position:relative;
	overflow:hidden;
}
.search_tab ul li{
	float:left;
	width:calc(50% - 0.1rem);
	margin-right:0.2rem;
}
.search_tab ul li:nth-child(2n){
	margin-right:0;
}
.search_tab a{
	display:block;
	padding:0.2rem;
	font-size:0.28rem;
	text-align:center;
	
	border-top:1px solid #333;
	border-left:1px solid #333;
	border-right:1px solid #333;
}
.search_tab a:hover{
	text-decoration:none;
}
.search_tab a.search_btn{
	background:#FFF;
	color:#333;
}
.search_tab a.search_btn_active{
	background:#333;
	color:#FFF;
}
</style>
<div class="search_tab">
<ul>
<?php
if(isset($_GET['sort']) && $_GET['sort'] == "new"):
?>
<li><a href="<?php echo $search_base_url; ?>" class="search_btn">標準</a></li>
<li><a href="<?php echo $search_base_url."&sort=new";?>" class="search_btn_active">新着順</a></li>
<?php
else:
?>
<li><a href="<?php echo $search_base_url; ?>" class="search_btn_active">標準</a></li>
<li><a href="<?php echo $search_base_url."&sort=new";?>" class="search_btn">新着順</a></li>
<?php
endif;
?>
</ul>
</div>
</div>



            
            <p class="count"><?php if($res_data['first'] != "")	echo $res_data['count']."件中".$res_data['first']."-".$res_data['end']."件表示"; ?></p>
        </div><!-- search-heading -->
        <div class="search-body">

<?php foreach($res_data['events'] as $k => $row): ?>
            <div class="sr-item">
              <h3 class="headline03"><?php echo $row['event_name']; ?></h3>
              <div class="tours">
                <div class="panel">
<?php /*
                  <div class="panel-heading">
                    <span class="orange">WEB予約限定プラン</span>
                    <span class="green w02">クレジットカード決済</span>
                  </div>
*/ ?>
                  <div class="panel-body">
                    <div class="tourist">
                      <a href="/sp/book/calendar.php?eventid=<?php echo $row['event_code']; ?>">
                        <p class="image">
                        <?php 
						$image_base = "https://img.poke.co.jp/media/";
						if( getimagesize($image_base . $row['picture1']) ):
						?>
                        <img src="<?php echo $image_base . $row['picture1']; ?>" alt=" ">
                        <?php endif; ?>
                        </p>
                        <div class="ttl">
                          <p class="desc">
                          <?php
                          $offset = 68;
						  $tmp = strip_tags($row['web_event_contents']);
						  if(mb_strlen( $tmp ,'UTF-8') > $offset)
								$tmp = mb_substr( $tmp , 0 , $offset , 'UTF-8')."...";
						 
						   echo $tmp;
						  
						  #echo mb_substr(strip_tags($row['web_event_contents']), 0, 250, "UTF-8"); 
						  ?>
                          </p>
                          <?php /*
                          <p class="rate01"><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><small>（21件）</small></p>
                           */ ?>
                        </div>
                      </a>
                    </div>
                    <div class="vote-bl"></div>
                    <table>
                      <tr>
                        <th>期間</th>
                        <td><?php echo $row['stock_date_from']."〜".$row['stock_date_to'];?><!--2016年05月15日 〜 2018年03月31日--></td>
                      </tr>
<?php /*                      <tr>
                        <th>エリア</th>
                        <td>
						<?php 
						foreach($row['area_id'] as $area_id):
						echo "<div>".$m_area[$area_id]."</div>";
						endforeach;
						?>
						<!--飯田橋・神楽坂・後楽園・水道橋--></td>
                      </tr> */ ?>
                      <tr>
                        <th>料金</th>
                        <td><?php echo number_format($row['adult_fee']); ?>円〜
                        <?php
                        if ($row['abstract'] == null) {
                          echo "&nbsp;";
                        } else {
                          echo htmlspecialchars("（" . $row['abstract'] . "）");
                        }
                        ?></td>
                      </tr>
                      <tr>
                        <th class="th">テーマ</th>
                        <td class="td">
                        <?php
                        if( count($row['themes']) > 0):
                            foreach($row['themes'] as $themeid => $themename):
                        ?>
                        <a href="/sp/book/eventlist.php?theme_id[]=<?php echo $themeid; ?>"><?php echo $themename; ?></a>　
                        <?php
                            endforeach;
                        endif;
                        ?>
                        
                        <?php #echo $themeText; ?></td>
                      </tr>
                      <tr>
                        <th class="th">特集</th>
                        <td class="td">
                        <?php
                        if( count($row['purposes']) > 0):
                            foreach($row['purposes'] as $purposeid => $purposename):
                        ?>
                        <a href="/sp/<?php echo str_replace("_","/",$purposeid); ?>/"><?php echo $purposename; ?></a>　
                        <?php /* <a href="/sp/book/eventlist.php?purpose_id[]=<?php echo $purposeid; ?>"><?php echo $purposename; ?></a>　*/ ?>
                        <?php
                            endforeach;
                        endif;
                        ?>
                        </td>
                      </tr>
                      <tr>
                        <th class="th">エリア</th>
                        <td class="td">
                        <?php
                        if( count($row['areas']) > 0):
							foreach($row['areas'] as $areaid => $areaname):
                        ?>
                        <a href="/sp/book/eventlist.php?area_id[]=<?php echo $areaid; ?>"><?php echo $areaname; ?></a>　
                        <?php
                            endforeach;
                        endif;
                        ?>
                        
                        <?php #echo $themeText; ?></td>
                      </tr>
                    </table>
                  </div>
                  <div class="vote-bl">
<?php /*
                    <a class="like" href="#"><i class="fa fa-heart"></i>行きたい！</a><span class="amount">2114</span>
*/ ?>
                    <a class="favorite01" href="#" onclick="setFavorite('<?php echo str_replace("P","",$row['event_code'])*1  ; ?>');return false;"><img src="/sp/common2/img/common/favorite.png" alt="favorite">お気に入りリストに追加</a>
                  </div>
                  <p class="text-center"><a class="btn-detail" href="/sp/book/calendar.php?eventid=<?php echo $row['event_code']; ?>">詳細を見る</a></p>
                </div>
              </div>
            </div>
<?php endforeach; ?> 
           
         <div class="pager-last">



            <div class="pagination">
              <ul>
              <li class="prev">
<?php if($res_data['page'] > 1): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] - 1 ).$sort;?>">Prev</a>
<?php endif; ?>
                </li>
<?php for($i=1;$i<=$res_data['max_page']; $i++): ?>
<li<?php if($res_data['page'] == $i) echo ' class="active"'; ?>><a href="<?php echo $search_base_url."&page=".$i.$sort;?>"><?php echo $i; ?></a></li>
<?php endfor; ?>
                <li class="next">
<?php if($res_data['page'] < $res_data['max_page']): ?>                
                <a href="<?php echo $search_base_url."&page=".($res_data['page'] + 1).$sort;?>">Next</a>
<?php endif; ?>   
                </li>
              </ul>
            </div>


<?php else: ?>

<div class="no_result">
ご指定の検索条件に該当するツアー・イベントは見つかりませんでした。<br>
条件を変えて、再度検索ください。
</div>

<?php endif; ?>


            </div>
          </div><!-- search-body -->
        </div><!-- / .search-results -->   


<?php

if(count($list) == 0):
	require_once("2018/feature.html");
endif;

?>

<div class="mb20"></div>




      <?php require_once("2018/footer.html"); ?>
<?php endif; ?>