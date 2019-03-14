<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
    <meta name="description" content="<?= $web_description ?>">
    <meta name="keywords" content="<?= $web_keyword ?>">
    <?php
        if($searchengine_index == 0){
          echo '<meta name="robots" content="noindex" />'.PHP_EOL;
        }
      ?>
    <title><?= $pagetitle ?></title>
<?php
define("H1TXT",$pagetitle);

?>


    <link rel="stylesheet" href="/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/common2/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="/common2/css/slick.css">
    <link rel="stylesheet" href="/common2/css/style.css?v=0703">

    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("/_data/tags/head_tag.php"); ?>

<style>
.slick-track{
	overflow:hidden;
}
</style>

<?php 
if($alternate_tag != ""): 
	echo $alternate_tag."\n";
endif; ?>
  </head>
  <body>
  <?php include_once("/_data/tags/body_tag.php"); ?>
    <div class="container" id="container">
      <?php include_once("include/2018/header.html"); ?>

      <div class="main">
        <div class="wrapper">

<?php
$themelink = $url_currentdir."eventlist.php?theme_id[]=".$themaid;
?>
          <ul class="breadcrumb">
<?php if(isset($breadcrumbArray[1])): 
$num = count($breadcrumbArray[1]);
$cnt=0;
foreach($breadcrumbArray[1] as $k => $v):
$cnt++;
if($num == $cnt):
?>
			<li><?php echo $v['linked_name']; ?></li>
<?php
else:
?>
			<li><a class="trans" href="<?php echo $v['linked_url']; ?>"><?php echo $v['linked_name']; ?></a></li>
<?php
endif; 
endforeach;
			else: ?>
            <li><a class="trans" href="/">ポケカルTOP</a></li>
            <li><a class="trans" href="<?= $themelink; ?>"><?= $themaname; ?><?=$lbl->printL('ichiran')?></a></li>
            <li><?= $eventname ?></li>
<?php endif; ?>
          </ul>

<?php
############################
#
#　終了イベントの場合
#
############################
  if($isClosed){
	$similar_list = $similar_events->getList();
	if(count($similar_list) > 0 ){
	  echo '<div class="mb10 mt15" align="center"><img src="img/img_expired2.png" /></div>';
	  echo '<div class="similarevent">';
	  foreach ((array)$similar_list as $val) {
		echo '<div class="mb10"><img src="img/icon_flag.png" width="22" class="icon_flag" />&nbsp;<a href="'.$val['link'].'" class="a_renew">'.mb_strimwidth($val['title'], 0, 88, "・・・", "utf8").'</a></div>';
	  }
	  echo '</div>';
	}else{
	  echo '<div class="mb10 mt15" align="center"><img src="img/img_expired1.png" /></div>';
	}
  }
?>          
          
          <div class="anchorWrap">
            <ul class="js-anchor anchor-list">
              <li>
                <a href="#anchor01">
                  <span class="txt-anchor">おすすめポイント</span>
                  <span class="icon"></span>
                </a>
              </li>
              <li>
                <a href="#anchor02">
                  <span class="txt-anchor">ツアー詳細</span>
                  <span class="icon"></span>
                </a>
              </li>
              <li>
                <a href="#anchor03">
                  <span class="txt-anchor">出発日カレンダー</span>
                  <span class="icon"></span>
                </a>
              </li>
              <li>
                <a href="#anchor04">
                  <span class="txt-anchor">お問い合わせ</span>
                  <span class="icon"></span>
                </a>
              </li>
            </ul>
          </div>
          <div id="anchor01" class="tour-cotent">
            <div class="title-top clearfix">
              <!--<span class="txt-left"><?= $themaname; ?></span>-->
              
              <?php foreach($themes as $v): ?>
              <span class="txt-left"><?= $v['name']; ?></span>
              <?php endforeach; ?>
              
              
<?php /*              <span class="txt-right"><?= $lbl->printL('shohinbango') ?> <?= $eventid; ?></span> */ ?>
            </div>
            
            <div class="mt5"><?= $lbl->printL('shohinbango') ?> <?= $eventid; ?></div>
            <div class="mb10"></div>
            
            <h1 class="sec-title"><?= $eventname ?><?php /* 【浜松町集合・バス送迎】目黒川桜回廊貸切クルーズ＆隅田川千本桜クルーズ*/ ?></h1>

<!-- 必要か不明 -->
<?php
if ($keybannerpath != null) {
  ?>

                            <div class="keyimg">
                              <a href="#calendar"><img src="<?= MAP_URL . $keybannerpath ?>" alt="<?= $keybannercaption ?>" /></a>
                            </div>
  <?php
}
?>


            <ul class="list-thumbnail">
<?php
if ($picture1thumbpath != null) {
?>
              <li class="item">
                <img src="<?= MAP_URL . $picture1thumbpath; ?>" alt="thumb01" class="group1" title="">
                <div class="desc pl0" align="center"><?= $picture1caption; ?></div>
              </li>
<?php
}
if ($picture2thumbpath != null) {
?>
              <li class="item">
                <img src="<?= MAP_URL . $picture2thumbpath; ?>" alt="thumb02" class="group1" title="">
                <div class="desc" align="center"><?= $picture2caption; ?></div>
              </li>
<?php
}
if ($picture3thumbpath != null) {
?>
              <li class="item">
                <img src="<?= MAP_URL . $picture3thumbpath; ?>" alt="thumb03" class="group1" title="">
                <div class="desc" align="center"><?= $picture3caption; ?></div>
              </li>
<?php
}
?>
            </ul>


<div class="list-desc-detail">
	<div class="desc-detail">
        <div class="desc-item">
      		<p><?= $eventcontents; ?></p><?= $eventsubstance; ?>
    	</div>
	</div>
</div>


<?php /*
            <div class="list-desc-detail">
              <div class="desc-detail">
                <div class="desc-item">
                  <h3 class="title">●目黒川桜回廊貸切クルーズ＆隅田川千本桜クルーズ 2大桜名所めぐり</h3>
                  <p class="item-txt01">「浜松町集合・バス送迎」都内最大級の2つの桜回廊をお花見クルーズ！<br>1日で2つの大人気クルーズを満喫！ポケカルおすすめ「お花見航路」</p>
                </div>
                <div class="desc-item">
                  <h3 class="title">●圧巻！船から見上げる「目黒川桜回廊貸切クルーズ」</h3>
                  <div class="desc">
                    <p>都内最大級の桜回廊！<br>約3.8kmの桜並木が目黒川を覆うように両岸から咲き誇ります！<br>水上の特等席より見上げる「桜のトンネル」をお楽しみください。</p>
                    <p>・目黒川桜回廊貸切クルーズの予定航路<br>浜松町集合場所 ⇒ 天王洲 or 北品川乗船場までバス送迎<br>天王洲 ⇒ 昭和橋 ⇒ 御成橋 ⇒ 五反田大橋 ⇒ 市場橋 ⇒ 太鼓橋付近にて折り返し ⇒ 乗船場帰港</p>
                  </div>
                  <ul class="note-list">
                    <li>※太鼓橋（目黒雅叙園）付近にて折り返しいたします。（クルーズ時間 約70分予定）</li>
                    <li>※航路は、当日の状況や天候状況等により変更になる場合がございます。</li>
                  </ul>
                </div>
                <div class="desc-item">
                  <h3 class="title">●隅田川の両岸を覆う千本桜堤へ「隅田川千本桜クルーズ」</h3>
                  <div class="desc">
                    <p>東京の桜景色の代名詞！<br>隅田川の両岸に咲く「隅田川千本桜（江戸時代から続く1,000本の桜並木）」をお楽しみください！</p>
                    <p>・隅田川千本桜クルーズの予定航路<br>日の出乗船場 ⇒ 勝鬨橋 ⇒ 佃大橋 ⇒ 永代橋 ⇒ 清洲橋 ⇒ 両国橋 ⇒ 厩橋 ⇒ 吾妻橋 ⇒ 浅草乗船場</p>
                    <ul class="note-list">
                      <li>※航路は、当日の状況や天候状況等により変更になる場合がございます。</li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="desc-detail desc-detail01">
                <div class="desc-item">
                  <h3 class="title01">【当日のスケジュール】</h3>
                  <ol class="schedule-day">
                    <li>①浜松町（9：00集合）＝目黒川桜回廊クルーズ（9：30～10：40）＝隅田川千本桜クルーズ（11：40～12：30）＝浅草乗船場着後、終了</li>
                    <li>②浜松町（10:20集合）＝目黒川桜回廊クルーズ（11:00～12:10）＝隅田川千本桜クルーズ（13:10～14:00）＝浅草乗船場着後、終了</li>
                    <li>③浜松町（12:10集合）＝目黒川桜回廊クルーズ（12:40～13:50）＝隅田川千本桜クルーズ（14:40～15:30）＝浅草乗船場着後、終了</li>
                  </ol>
                  <ul class="note-list">
                    <li>※当日の状況により、スケジュールが変更になる場合がございます。</li>
                    <li>※集合場所から各乗船場までは、[バス送迎]いたします。</li>
                  </ul>
                </div>
              </div>
              <div class="desc-detail">
                <div class="desc-item">
                  <h3 class="title01">【料金】</h3>
                  <p class="desc">
                    大人：平日 ￥7,980、土日 ￥8,480<br>
                    子供（小学生）：平日・土日 ￥6,980
                  </p>
                  <ul class="note-list">
                    <li>※未就学児童は参加出来ません。</li>
                  </ul>
                </div>
              </div>
              <div class="desc-detail">
                <div class="desc-item">
                  <h3 class="title01">【ご確認事項】</h3>
                  <ul class="list-txt-style">
                    <li>・桜の開花状況に関わらず、開催決定後は、ツアーを開催いたします。</li>
                    <li>・子供料金（小学生）となります。未就学児童は参加出来ません。</li>
                    <li>・桜の開花状況に関わらず、開催決定後は荒天の場合を除き開催いたします。</li>
                    <li>・雨天も開催いたします。雨天時には各自にて傘以外の雨具（レインコートなど）の準備をお願いいたします。</li>
                    <li>・当ツアーにはお食事が付きません。軽食程度（おにぎり等）なら、お客様の膝下にて各船内でお召し上がりいただけます。その際のゴミはお 持ち帰りいただきます。</li>
                    <li>・目黒川クルーズ＆隅田川クルーズに関するお問い合わせは「ポケカル」にお問い合わせください。</li>
                  </ul>
                </div>
                <div class="desc-item">
                  <h3 class="title01">【荒天等により桜クルーズが中止の場合】</h3>
                  <ul class="list-txt-style">
                    <li>・目黒川桜クルーズが中止の場合は、参加費より￥2,000をご返金いたします。その際「東京タワー大展望台見学」に変更し隅田川クルーズへ ご案内いたします。</li>
                    <li>・目黒川桜クルーズ・隅田川桜クルーズの2つのクルーズが中止の場合は参加費全額を払い戻しいたします。</li>
                  </ul>
                  <ul class="note-list">
                    <li>※ご返金に関しては、後日、ポケカルよりご連絡をいたします。</li>
                  </ul>
                </div>
              </div>
            </div>
*/ ?>


          </div>
          <div id="anchor02" class="tour-details">
            <h2 class="headline04">ツアー詳細</h2>
            <div class="panel">
              <div class="panel-heading">
                <div class="left">
<?php /*
                  <span class="orange">WEB予約限定プラン</span>
                  <span class="green">クレジットカード決済</span>
*/?>
                </div>
                <div class="right">
<?php /*                  <a class="like" href="#"><i class="fa fa-heart"></i>行きたい！</a><span class="amount">000</span>*/ ?>
                  <a class="favorite01" href="javascript:void(0)" onclick="setFavorite('<?php echo $eventid4history ; ?>');return false;"><img src="/common2/img/common/icon_favorite.png" alt="favorite">お気に入りリストに追加</a>
                </div>
              </div>
              <div class="panel-body">
                <table>
                  <tr>
                    <th>テーマ</th>
                    <td class="col01 vrmiddle">
                    <?php
					if( count($themes) > 0):
						foreach($themes as $theme):
					?>
                    <a href="/book/eventlist.php?theme_id[]=<?php echo $theme['themeid']; ?>"><?php echo $theme['name']; ?></a>　
                    <?php
						endforeach;
					endif;
					?>
                    </td>
                    <th>特集</th>
                    <td>
                    <?php
					if( count($purposes) > 0):
						foreach($purposes as $purpose):
					?>
                    <a href="/<?php echo str_replace("_","/",$purpose['purposeid']); ?>/"><?php echo $purpose['name']; ?></a>　
                    <?php /*<a href="/book/eventlist.php?purpose_id[]=<?php echo $purpose['purposeid']; ?>"><?php echo $purpose['name']; ?></a>　*/ ?>
                    <?php
						endforeach;
					endif;
					?>
                    </td>
                  </tr>
                  <tr>
                    <th>商品番号</th>
                    <td class="col01"><?= $eventid; ?><!--P0082--></td>
                    <th>口コミ</th>
                    <td class="rate01">
                    <?php /*
                    <span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><span><img src="/common2/img/common/icon_star.png" alt="★"></span><small>（<a href="#">21件</a>）</small>
					*/ ?>
					</td>
                  </tr>
                  <tr>
                    <th>開催時間</th>
                    <td class="col01"><?=$starttime;?><?=$lbl->printL('kara')?><?=$endtime;?>&nbsp;<!--2016年05月15日 ～ 2018年03月31日 --></td>
                    <th>エリア</th>
                    <td>
					<?php
					if( count($areas) > 0):
						foreach($areas as $area):
					?>
                    <a href="/book/eventlist.php?area_id[]=<?php echo $area['areaid']; ?>"><?php echo $area['name']; ?></a>　
                    <?php
						endforeach;
					endif;
					?>
					
					<?php #echo $areaname; ?><!--神奈川：横浜--></td>
                  </tr>
                  <tr>
                    <th>料金</th>
                    <td colspan="3"> <?= $adultfee; ?><?= $optionsetumei; ?><!--7,650円～（乗船料+お食事）--></td>
                  </tr>
                  <tr>
                    <th>スケジュール</th>
                    <td colspan="3">
                    <ul class="schedule" style="margin-top:-13px;"><?= $schedulelist; ?></ul>
                    
                    <!--
                      <ul class="schedule">
                        <li>09:00：（JR浜松町駅北口）【汐留芝離宮ビルディング前】広場付近にて集合・受付け</li>
                        <li>09:30：【目黒川桜回廊クルーズ】（※予定時間　9:30～10:40）</li>
                        <li>11:40：【隅田川千本桜クルーズ】（※予定時間　11:40～12:30）</li>
                        <li>12:30：【浅草・吾妻橋乗船場】到着後、終了となります。</li>
                      </ul>
                      <ul class="note-list fs">
                        <li>※当日の状況によりスケジュールが変更になる場合がございます。添乗員の指示に従ってください。</li>
                        <li>※【集合場所】から【各乗船場】までは、バス送迎でご案内いたします！</li>
                      </ul>
					  -->
                    </td>
                  </tr>
                  <tr>
                    <th>最小催行人員</th>
                    <td colspan="3"><?= $minsaikounum; ?><!--25人--></td>
                  </tr>
                  <tr>
                    <th>集合場所</th>
                    <td colspan="3"><ul class="schedule" style="margin-top:-13px;"><?= $place ?></ul><!--【横浜港大さん橋、国際線ターミナル２F】ロイヤルウイングチケットカウンター<a class="map" href="#">地図</a>--></td>
                  </tr>
<?php if(isset($guide)):
$eval_int = floor($guide['evaluation_average']);
$eval_decimals = ($guide['evaluation_average'] - $eval_int) >= 0.5 ? "5" : "0";
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

	<div class='guide_eventlist_prof'><a href='/guide-member/profile/?guide_id=<?php echo $guide['id'];?>'><img src='<?php echo IMAGE_BASE.$guide['image_profile']; ?>' alt='<?php echo $guide['sei']." ".$guide['mei']; ?>'></a></div>

</div>
<div class="guide_eventlist_r">

	<div class="guide_eventlist_name"><?php echo $guide['sei']." ".$guide['mei']; ?></div>
    <div class='guide_eventlist_eval'><span class='rate rate<?php echo $eval_int.$eval_decimals;?>'></span><a href='/guide-member/profile/?guide_id=<?php echo $guide['id'];?>'>(<?php echo $eval_int.".".$eval_decimals;?>)</a>　　　　<img src='/images/guide2018/icon_guide2.png' width='23'>&nbsp;お客様の声<a href='/guide-member/profile/?guide_id=<?php echo $guide['id'];?>'>(<?php echo $guide['cnt'];?>)</a></div>
    <div class="p14">
    『開催日<?php echo $guide['event_date'];
	if($guide['thirdid']):
		echo " (".$guide['thirdid'].")";
	endif;?>
	』 を担当します<br />
ガイド歴:<?php echo $guide['guide_history']; ?> ／ 得意ジャンル:<?php echo $guide['guidegenre']; ?>
	</div>
    

</div>
</div>

</td>
</tr>
<?php endif; ?>
                </table>
              </div>
            </div>
          </div>
          <div id="anchor03" class="departure-date">
            <h2 class="headline04"><span>出発日カレンダー</span><span class="txt-small">"申し込みへ"をクリックすると申し込み画面へ進みます</span></h2>
            <div class="calendar-content">
              <p class="desc">お申し込みは、開催日の<?= $uketukekanoubi; ?><!--3日前迄-->になります。</p>
              <div class="desc-date">
                <!-- <img src="/common2/img/calendar/calendar.png" alt=""> -->
                <div class="list-btn-calendar">
                  <div class="month-prev">
					<?php
                    //前月リンクボタン制御
                    if ($base_year == $year && $base_month == $month) {
                      $tmp_pre .= "<span>先月</span><img src=\"/common2/img/calendar/prev_calendar.png\" alt=\"Prev\" class=\"icon-calendar\" />";
                    } else {
                      $tmp_pre .= "<a href=\"" . $url_mypage . "?eventid={$eventid}&y={$pre_year}&m={$pre_month}&by={$base_year}&bm={$base_month}#calendar\" class=\"btn-month-prev\"><span>先月</span><img src=\"/common2/img/calendar/prev_calendar_on.png\" alt=\"Prev\" class=\"icon-calendar\" /></a>";
                    }
                    echo $tmp_pre . "\n";
                    ?>
                    <!--
                    <a href="#" class="btn-month-prev">
                      <span>先月</span>
                      <img src="/common2/img/calendar/prev_calendar.png" alt="Prev" class="icon-calendar">
                    </a>
                    -->
                  </div>
                  <div class="list-btn-month2" id="list-btn-month2">
                  <?php
				  //固定月タブ表示
				  for ($i = 0; $i < count($n_month); $i++) {
					if($i == 0 || $i == 6) $tmp_month .= "<p>"; 
					  
					if ($n_month[$i] == $month) {
					  $tmp_month .= "<a href=\"" . $url_mypage . "?eventid={$eventid}&y={$n_year[$i]}&m={$n_month[$i]}&by={$base_year}&bm={$base_month}#calendar\" class=\"btn-month2 active\">" . $lbl->printL(number_format($n_month[$i])."gatsu") . "</a>" . "\n";
					} else {
					  $tmp_month .= "<a href=\"" . $url_mypage . "?eventid={$eventid}&y={$n_year[$i]}&m={$n_month[$i]}&by={$base_year}&bm={$base_month}#calendar\" class=\"btn-month2\">" . $lbl->printL(number_format($n_month[$i])."gatsu") . "</a>" . "\n";
					}
					
					if($i == 5 || $i == 11)	$tmp_month .= "</p>";
				  }
				  echo $tmp_month . "\n";
				  ?>
                  
                  
                  </div>
                  <div class="month-next">
                  	<?php
					//翌月リンクボタン制御
					if ($year == $last_year && $month == $last_month) {
					  $tmp_next .= "<span>来月</span><img src=\"/common2/img/calendar/next_calendar.png\" alt=\"Next\" class=\"icon-calendar\" />";
					} else {
					  $tmp_next .= "<a href=\"" . $url_mypage . "?eventid={$eventid}&y={$next_year}&m={$next_month}&by={$base_year}&bm={$base_month}#calendar\"><span>来月</span><img src=\"/common2/img/calendar/next_calendar_on.png\" alt=\"Next\" class=\"icon-calendar\" /></a>";
					}
					echo $tmp_next . "\n";
					?>
					<!--
                    <a href="#" class="btn-month-next">
                      <span>来月</span>
                      <img src="/common2/img/calendar/next_calendar.png" alt="Next" class="icon-calendar">
                    </a>
                    -->
                  </div>
                </div>
                <!--<table cellpadding="0" cellspacing="0" class="schedule_body">-->

<style>
.calendar td{vertical-align:top !important;}
table.calendar a.subcription {
    margin: 5px auto 5px;
    display: block;
    text-indent: -9999px;
    width: 62px;
    height: 24px;
    background: url(<?php echo BOOKING_URL_SCHEME; ?>://img.poke.co.jp/images/event/btn_subcription_mini.jpg) no-repeat left top;
}
</style>
                <div class="full-year">
                <table cellpadding="0" cellspacing="0" class="calendar">
                                <tr class="calendar-header">
                                  <th width="14%"><?= $lbl->printL('nichiyobi'); ?></th>
                                  <th width="14%"><?= $lbl->printL('getsuyobi'); ?></th>
                                  <th width="14%"><?= $lbl->printL('kayobi'); ?></th>
                                  <th width="14%"><?= $lbl->printL('suiyobi'); ?></th>
                                  <th width="14%"><?= $lbl->printL('mokuyobi'); ?></th>
                                  <th width="14%"><?= $lbl->printL('kinyobi'); ?></th>
                                  <th width="14%"><?= $lbl->printL('doyobi'); ?></th>
                                </tr>
<?php


echo $tmp_cal;
?>
                              </table>
                </div>     
                <!--<div id="full-year" class="full-year"></div>-->
              </div>
              <p class="btn-print"><a href="#" onclick="print();"><img src="/common2/img/calendar/icon_print.png" alt="印刷する"></a></p>
            </div>
          </div>



<?php	
$cookie_name = "mrraf";
$asid = isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : "";
$txt = "";
if($asid == 1201):
?>

	<div class="mb40"></div>

<?php else: ?>
          <div id="anchor04" class="contact-us">
            <h2 class="headline04"><span>お問い合わせ</span><span class="txt-small">お申込み・お問い合わせはお電話でも承っております</span></h2>
            <div class="contact-content">
              <div class="info-contact">
                <div class="infor-left">
                  <p class="desc-left">お電話でのお申込み・お問い合わせはこちら</p>
                  <a href="tel:0356527072">
                    <img src="/common2/img/calendar/number_phone.png" alt="number phone">
                  </a>
                </div>
                <div class="infor-adress">
                  <p class="ttl">株式会社ポケカル</p>
                  <p>〒103-0015<br>東京都中央区日本橋箱崎町20-1 アンソレイエ・オオタ 5F</p>
                  <p>東京都知事登録旅行業第2-6811号<br>(社)全国旅行業協会正会員</p>
                </div>
              </div>
              <div class="time-contact">
                <span class="title">営業時間</span>
                <span class="desc-time">ポケカルお客様センター　9：00～18：00　年中無休</span>
              </div>
              <h3 class="title-contact">インターネットでのご予約は24時間365日受付ております。</h3>
              <div class="detail-contact">
                <h4 class="title">ご確認・ご注意事項</h4>
                <p class="desc">※「受付終了」の場合もご案内可能なご日程がある場合がございますので、お客様センターへお問い合わせください。キャンセル・変更については、お客様 センターまでお電話にてお問い合わせください。（キャンセル規約はこちら）当社の個人情報の取り扱いに関する詳細は<a href="#" class="link">こちら</a>の「個人情報保護方針」を ご覧ください。</p>
              </div>
            </div>
          </div>
          <div class="floating-menu">
            <div class="wrapper">
              <a class="btn-close-float" href="javascript:void(0);"><img src="/common2/img/calendar/btn_close.png" alt="CLOSE"></a>
              <ul class="js-anchor anchor-list">
                <li>
                  <a href="#anchor01">
                    <span class="txt-anchor">おすすめポイント</span>
                    <span class="icon"></span>
                  </a>
                </li>
                <li>
                  <a href="#anchor02">
                    <span class="txt-anchor">ツアー詳細</span>
                    <span class="icon"></span>
                  </a>
                </li>
                <li>
                  <a href="#anchor03">
                    <span class="txt-anchor">出発日カレンダー</span>
                    <span class="icon"></span>
                  </a>
                </li>
                <li>
                  <a href="#anchor04">
                    <span class="txt-anchor">お問い合わせ</span>
                    <span class="icon"></span>
                  </a>
                </li>
              </ul>
              <div class="list-contact">
                <a href="tel:0356527072" class="left item">
                  <img src="/common2/img/calendar/phone.png" alt="phone" class="icon-phone">
                  <span class="desc-contact-left">
                    <span class="number-phone">TEL：03-5652-7072</span>
                    <span class="time-open">ポケカルお客様センター 9：00~18：00</span>
                  </span>
                  <span class="btn-contact">年中無休</span>
                </a>
                <a href="#" class="right item">
                  <img src="/common2/img/calendar/tv.png" alt="tv" class="icon-phone">
                  <span class="book-online">インターネットでご予約</span>
                  <span class="btn-contact">24時間受付</span>
                </a>
              </div>
            </div>
          </div>

<?php endif; ?>

        </div>

<?php if($asid != 1201): ?>
        <section class="tour-search">
          <div class="wrapper">
            <?php include_once("include/2018/tour_search01.html"); ?>
          </div>
        </section>

        <section>
          <div class="wrapper">
            <?php include_once("include/2018/feature.html"); ?>
          </div>
        </section>

        <div class="wrapper">
          <div class="section-group">

<?php /*
            <section class="sec-ranking">
              <?php include_once("include/2018/ranking.html"); ?>
            </section>
*/ ?>
            <section class="sec-communicate">
              <?php include_once("include/2018/communicate.html"); ?>
            </section>
<?php /*
            <section class="sec-news-latest">
              <?php include_once("include/2018/news_latest.html"); ?>
            </section>

            <div class="sec-favorites">
              <?php include_once("include/2018/favorites.html"); ?>
            </div>
*/ ?>
			<?php #echo $data['favorites']; ?>
			<?php #echo $data['history']; ?>
            <section class="sec-video">
              <?php include_once("include/2018/video.html"); ?>
            </section>
<?php endif; ?>

          </div>
        </div>
      </div>

      <?php include_once("include/2018/footer.html"); ?>
