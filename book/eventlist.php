<?php
#ini_set( 'display_errors', 'On' );
//error_reporting(E_ALL ^ E_WARNING);
//if(strpos($_SERVER['HTTP_HOST'],'master') === false){
//    if (empty($_SERVER['HTTPS'])) {
//        header("HTTP/1.0 301 Moved Permanently");
//        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
//        exit;
//    }
//}

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

$paramurl_arr = array();
#foreach($params as $k => $v){
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
#print_r($res_data);



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
$noindex_flag = false;
if(isset($_GET['testview'])){
	print_r($_GET['theme_id']);
	print_r($_GET['area_id']);
}
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
		$noindex_flag = true;
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
		$noindex_flag = true;
	else:
	#（２）の場合
		$title = $m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
		$h1 = $m_area[$_GET['area_id'][0]]."のツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	
	endif;

else:
#（４）の場合
	$title = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアーならポケカルへ";
	$h1 = "指定条件に合致するツアー・イベント一覧｜日帰りバス旅行・観光ツアー";
	$noindex_flag = true;

endif;

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
    <meta name="description" content="検索結果。お手軽に日帰りで楽しめるツアー数を多数。ご予約はネットや電話で受付ております。">
    <meta name="keywords" content="検索結果,スポット,ツアー,日帰り">
    <title><?php echo $title; ?></title>
<?php if( isset($_GET['sort']) && $_GET['sort'] == "new"): ?>
<meta name="robots" content="noindex" />
<?php elseif( $noindex_flag == true): ?>
<meta name="robots" content="noindex" />
<?php endif; ?>
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
  </head>
  <body>
<?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

      <div class="wrapper">
        <ul class="breadcrumb">
          <li><a class="trans" href="../">日帰り旅行・ツアーTOP</a></li>
          <li>検索結果</li>
        </ul>
        <div class="search-results eventlist">
          <div class="main-title dropdown">▼ 詳細検索(クリックで検索フォームが開きます) ▼</div>
          <section class="tour-search accordion">
            <?php include_once("include/2018/tour_search02.html"); ?>
          </section>
          
          <h1 class="h1_search"><?php echo $h1; ?></h1>
          
          
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
<td colspan="3">

<div class="guide_eventlist_wrap">
<div class="guide_eventlist_l">

	<div class='guide_eventlist_prof'><a href='/guide-member/profile/?guide_id=<?php echo $res_data['guide'][$eventid]['id'];?>'><img src='<?php echo IMAGE_BASE.$res_data['guide'][$eventid]['image_profile']; ?>' alt='<?php echo $res_data['guide'][$eventid]['sei']." ".$res_data['guide'][$eventid]['mei']; ?>'></a></div>

</div>
<div class="guide_eventlist_r">

	<div class="guide_eventlist_name"><?php echo $res_data['guide'][$eventid]['sei']." ".$res_data['guide'][$eventid]['mei']; ?></div>
    <div class='guide_eventlist_eval'><span class='rate rate<?php echo $eval_int.$eval_decimals;?>'></span><a href='/guide-member/profile/?guide_id=<?php echo $res_data['guide'][$eventid]['id'];?>'>(<?php echo $eval_int.".".$eval_decimals;?>)</a>　　　　<img src='/images/guide2018/icon_guide2.png' width='23'>&nbsp;お客様の声<a href='/guide-member/profile/?guide_id=<?php echo $res_data['guide'][$eventid]['id'];?>'>(<?php echo $res_data['guide'][$eventid]['cnt'];?>)</a></div>
    <div class="p14">
    『開催日<?php echo $res_data['guide'][$eventid]['event_date'];
	if($res_data['guide'][$eventid]['thirdid']):
		echo " (".$res_data['guide'][$eventid]['thirdid'].")";
    endif;
    ?>』 を担当します<br />
ガイド歴:<?php echo $res_data['guide'][$eventid]['guide_history']; ?> ／ 得意ジャンル:<?php echo $res_data['guide'][$eventid]['guidegenre']; ?>
	</div>
    

</div>
</div>

</td>
</tr>
<?php endif; ?>
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



        <section class="tour-search">
          <?php /*include_once("include/2018/tour_search01.html"); */  ?>
        </section>
<?php /*
        <section>
          <!--#include virtual="../includes/feature.html" -->
        </section>
        <div class="section-group01">
          <section class="sec-ranking">
            <!--#include virtual="../includes/ranking.html" -->
          </section>
          <section class="sec-communicate">
            <!--#include virtual="../includes/communicate.html" -->
          </section>
          <section class="sec-news-latest">
            <!--#include virtual="../includes/news_latest.html" -->
          </section>
          <div class="sec-favorites">
            <!--#include virtual="../includes/favorites.html" -->
          </div>
          <section class="sec-video">
            <!--#include virtual="../includes/video.html" -->
          </section>
*/ ?>
        </div>
      </div>
      <?php include_once("include/2018/footer.html"); ?>