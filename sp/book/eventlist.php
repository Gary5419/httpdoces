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

$m_area = $cms->getAreamaster();
$m_theme = $cms->getThememaster();

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
#print_r($_GET);
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
#print_r($params);exit;

$paramurl_arr = array();
#foreach($params as $k => $v){
foreach($_GET as $k => $v){
	if($k == "page")	continue;
	if($k == "sort")	continue;
	if($k == "theme_id" || $k == "area_id" || $k == "pref_id" || $k == "purpose_id"):
		#$paramurl_arr[] = $k."[]=".$v;
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


/*
if (!function_exists('json_encode')) {
	require_once '_data/JSON.php';
	function json_encode($value) {
		$s = new Services_JSON();
		return $s->encodeUnsafe($value);
	}
	function json_decode($json, $assoc = false) {
		$s = new Services_JSON($assoc ? SERVICES_JSON_LOOSE_TYPE : 0);
		return $s->decode($json);
	}
}
			$res_data = json_decode($res,true);
*/

##################################
#
# title/h1 条件分岐
/*
●title
（１）テーマ(■■■)単数指定・・■■■のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ
（２）エリア(▲▲▲)単数指定・・▲▲▲のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ
（３）テーマ単数×エリア単数指定・・■■■｜▲▲▲のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ
（４）上記以外・・指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ
 
●h1⇒実装位置は、別途パワポ仕様を参照ください
（１）テーマ(■■■)単数指定・・「■■■のツアー・イベント一覧｜日帰りバス旅行・観光ツアー」
（２）エリア(▲▲▲)単数指定・・「▲▲▲のツアー・イベント一覧｜日帰りバス旅行・観光ツアー」
（３）テーマ単数×エリア単数指定・・「■■■｜▲▲▲のツアー・イベント一覧｜日帰りバス旅行・観光ツアー」
（４）上記以外・・「指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー」

*/
#
##################################
$m_pref["tokyo"] = "東京";
$m_pref["kanagawa"] = "神奈川";
$m_pref["saitama"] = "埼玉";
$m_pref["chiba"] = "千葉";
$m_pref["tochigi"] = "栃木";
$m_pref["gunma"] = "群馬";
$m_pref["ibaraki"] = "茨城";
$m_pref["shizuoka"] = "静岡";
$m_pref["yamanashinagano"] = "山梨・長野";
$m_pref["osakahyogo"] = "大阪・兵庫";
$m_pref["kyoto"] = "京都";
$m_pref["kyusyu"] = "九州";
$m_pref["etc"] = "その他の地域";

$noindex_flag = false;

if(isset($_GET['testview'])){
	print_r($_GET['theme_id']);
	print_r($_GET['area_id']);
}
if( isset($_GET['theme_id']) && count($_GET['theme_id']) == 1 && isset($_GET['pref_id']) && count($_GET['pref_id']) == 1):
	$title = $m_theme[$_GET['theme_id'][0]]."｜".$m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
	$h1 = $m_theme[$_GET['theme_id'][0]]."｜".$m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";

elseif( isset($_GET['theme_id']) && count($_GET['theme_id']) == 1):

	if( isset($_GET['pref_id']) && count($_GET['pref_id']) == 1):
	#（３）の場合
		$title = $m_theme[$_GET['theme_id'][0]]."｜".$m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_theme[$_GET['theme_id'][0]]."｜".$m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	elseif( isset($_GET['pref_id']) && count($_GET['pref_id']) > 1):
	#（４）の場合
		$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
		$noindex_flag = true;
	else:
	#（１）の場合
		$title = $m_theme[$_GET['theme_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_theme[$_GET['theme_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	endif;

elseif( isset($_GET['pref_id']) && count($_GET['pref_id']) == 1):

	if( isset($_GET['theme_id']) && count($_GET['theme_id']) == 1):
	#（３）の場合
		$title = $m_theme[$_GET['theme_id'][0]]."｜".$m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_theme[$_GET['theme_id'][0]]."｜".$m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	elseif( isset($_GET['theme_id']) && count($_GET['theme_id']) > 1):
	#（４）の場合
		$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
		$noindex_flag = true;
	else:
	#（２）の場合
		$title = $m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_pref[$_GET['pref_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	
	endif;

else:
#（４）の場合
	$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
	$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	$noindex_flag = true;

endif;
/*
if( isset($_GET['theme_id']) && count($_GET['theme_id']) == 1 && isset($_GET['area_id']) && count($_GET['area_id']) == 1):
	$title = $m_theme[$_GET['theme_id'][0]]."｜".$m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
	$h1 = $m_theme[$_GET['theme_id'][0]]."｜".$m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";

elseif( isset($_GET['theme_id']) && count($_GET['theme_id']) == 1):

	if( isset($_GET['area_id']) && count($_GET['area_id']) == 1):
	#（３）の場合
		$title = $m_theme[$_GET['theme_id'][0]]."｜".$m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_theme[$_GET['theme_id'][0]]."｜".$m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	elseif( isset($_GET['area_id']) && count($_GET['area_id']) > 1):
	#（４）の場合
		$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	else:
	#（１）の場合
		$title = $m_theme[$_GET['theme_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_theme[$_GET['theme_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	endif;

elseif( isset($_GET['area_id']) && count($_GET['area_id']) == 1):

	if( isset($_GET['theme_id']) && count($_GET['theme_id']) == 1):
	#（３）の場合
		$title = $m_theme[$_GET['theme_id'][0]]."｜".$m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_theme[$_GET['theme_id'][0]]."｜".$m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	elseif( isset($_GET['theme_id']) && count($_GET['theme_id']) > 1):
	#（４）の場合
		$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	else:
	#（２）の場合
		$title = $m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	
	endif;

else:
#（４）の場合
	$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
	$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";

endif;
*/
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
    <meta name="description" content="検索結果。お手軽に日帰りで楽しめるツアー数を多数。ご予約はネットや電話で受付ております。">
    <meta name="keywords" content="検索結果,スポット,ツアー,日帰り">
    <title><?php echo $title; ?></title>
<?php if( isset($_GET['sort']) && $_GET['sort'] == "new"): ?>
<meta name="robots" content="noindex" />
<?php elseif( $noindex_flag == true): ?>
<meta name="robots" content="noindex" />
<?php endif; ?>
    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css?v=20181022">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("tags/head_tag.php"); ?>


<meta name="format-detection" content="telephone=no" />
</head>
 <body>
<?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

      <div class="main">
        <ul class="breadcrumb">
          <li><a href="../">日帰り旅行・ツアーTOP</a></li>
          <li>検索結果</li>
        </ul>

<style>
.h1_search{
	color:#333333;
	font-size:0.24rem;
	margin: 0 0.21rem 0.2rem 0.21rem;
	
	box-sizing:border-box;
}
</style>
        <h1 class="h1_search"><?php echo $h1; ?></h1>

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
<?php if(isset($res_data['guide'][str_replace("P","",$row['event_code'])*1])):
$eventid = str_replace("P","",$row['event_code'])*1;
$eval_int = floor($res_data['guide'][$eventid]['evaluation_average']);
$eval_decimals = ($res_data['guide'][$eventid]['evaluation_average'] - $eval_int) >= 0.5 ? "5" : "0";
?>
<style>

</style>
<tr>
<th class="guide_eventlist_th">
担当ガイド
</th>
<td class="td">

<div class="guide_eventlist_wrap">
<div class="guide_eventlist_l">

	<div class='guide_eventlist_prof'><a href='/guide-member/profile/?guide_id=<?php echo $res_data['guide'][$eventid]['id'];?>'><img src='<?php echo IMAGE_BASE.$res_data['guide'][$eventid]['image_profile']; ?>' alt='<?php echo $res_data['guide'][$eventid]['sei']." ".$res_data['guide'][$eventid]['mei']; ?>'></a></div>

</div>
<div class="guide_eventlist_r">

	<div class="guide_eventlist_name"><?php echo $res_data['guide'][$eventid]['sei']." ".$res_data['guide'][$eventid]['mei']; ?></div>
    <div class='guide_eventlist_eval'><span class='rate rate<?php echo $eval_int.$eval_decimals;?>'></span><a href='/guide-member/profile/?guide_id=<?php echo $res_data['guide'][$eventid]['id'];?>'>(<?php echo $eval_int.".".$eval_decimals;?>)</a></div>
    <div class="guide_eventlist_eventdate">
    
    <div class="c_cc0000">『開催日<?php echo $res_data['guide'][$eventid]['event_date'];
	if($res_data['guide'][$eventid]['thirdid']):
		echo " (".$res_data['guide'][$eventid]['thirdid'].")";
    endif;
    ?>』 を担当します</div>
ガイド歴:<?php echo $res_data['guide'][$eventid]['guide_history']; ?>
	</div>
    

</div>
</div>

</td>
</tr>
<?php endif; ?>
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
            
        <section>
<?php
/*
foreach($res_data as $k => $v){
	echo $k."---".htmlspecialchars($v)."<br>\n";
	
}
*/
?>
</section>
</div>
</div>


<div class="mb20"></div>


<?php require_once("2018/footer.html"); ?>