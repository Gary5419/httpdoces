<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
    <meta name="description" content="<?=$web_description?>">
    <meta name="keywords" content="<?=$web_keyword?>">
<?php
  if($searchengine_index == 0){
    echo '<meta name="robots" content="noindex" />'.PHP_EOL;
  }
?>
    <title><?=$pagetitle?></title>

    <link rel="stylesheet" href="/sp/common2/css/font-awesome.min.css">
    <link rel="stylesheet" href="/sp/common2/css/jquery-ui.css">
    <link rel="stylesheet" href="/sp/common2/css/slick.css">
    <link rel="stylesheet" href="/sp/common2/css/style.css?v=20181022">
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="cleartype" content="on">
    <![endif]-->
      <?php include_once("tags/head_tag.php"); ?>
    <style>
	#date{/* margin:0 5px 0;*/}
#date p{
/*
	font-size:13px; margin:0;
*/
}

.date_slide{
	width:298px;
	overflow:hidden;}
.date_wrap{
	width:9999px;}

.datebox{
	width:298px;
	float:left;
	margin:10px 0 0 0;}

#date table{
	width:298px;
	border:#000 solid 1px;
	border-radius: 3px;
	border-collapse:separate;}
#date table th{
	width:29%;
	border-left:#000 solid 1px;
	background:#f2f2f2;
	padding:4px 0 3px;}
#date table tr th:nth-child(2){
	width:42%;}

#date table td{
	text-align:center;
	border-top:#000 solid 1px;
	border-left:#000 solid 1px;
	vertical-align:middle;
	padding:5px 0 4px;
	background:#fff;}

#date table td img{
	padding:4px 0 0 0;}
#date table td.price span{
	display:block; margin:0 2px 0;
	border-top:#000 dotted 1px;
	padding:4px 0;}
#date table td.price span:first-child{
	border-top:none;}
.day{font-weight:bold;}
.saturday{color:#29abE2;}
.sunday{color:#FF7378;}

#date table th:first-child,#date table td:first-child{
	border-left:none;}

#date .datebox p{
	margin:10px 0 0 0;}
#date .datebox p.back{
	float:left;}
#date .datebox p.next{
	text-align:right;}
.bg_f7f7f7{
	background:#F7F7F7;
}
	</style>

<?php 
if($canonical_tag != ""): 
	echo $canonical_tag."\n";
endif; ?>
  </head>
  <body>
  <?php include_once("tags/body_tag.php"); ?>
    <div class="container" id="container">
      <!-- HEADER -->
      <?php require_once("2018/header.html"); ?>
      <!-- END HEADER -->

      <div class="main">

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
			<li><a href="<?php echo $v['linked_url']; ?>"><?php echo $v['linked_name']; ?></a></li>
<?php
endif; 
endforeach;
			else: ?>
            <li><a href="/">ポケカルTOP</a></li>
            <li><a href="<?= $themelink; ?>"><?= $themaname; ?><?=$lbl->printL('ichiran')?></a></li>
            <li><?= $eventname ?></li>
<?php endif; ?>
          </ul>


<?php /*
        <ul class="breadcrumb">
          <li><a href="../">ポケカルTOP</a></li>
          <li class="mr0"><a href="<?=$url_currentdir."eventlist.php?themaid=";?><?=$themaid;?>"><?=$themaname;?></a></li>
          <li class="pl20"><?=$eventname?></li>
        </ul>
*/ 
?>

        <section>
        
		<?php echo $closed_html ; ?>
        
          <h1 class="headline01 fs"><?=$eventname?></h1>

          <div class="article-slider">
            <div class="slider-large slider-for01">
              <div class="item"><img src="//img.poke.co.jp/media/transfer/img/eventid_<?php echo str_replace("P","",$eventid)*1; ?>/eventid_<?php echo str_replace("P","",$eventid)*1; ?>_pic1.jpg" id="eventphoto" height="160" /><!--<img src="/sp/common2/img/book/sl01.jpg" alt="image">--></div>
              <div class="item"><img src="//img.poke.co.jp/media/transfer/img/eventid_<?php echo str_replace("P","",$eventid)*1; ?>/eventid_<?php echo str_replace("P","",$eventid)*1; ?>_pic2.jpg" id="eventphoto" height="160" /><!--<img src="/sp/common2/img/book/sl02.jpg" alt="image">--></div>
              <div class="item"><img src="//img.poke.co.jp/media/transfer/img/eventid_<?php echo str_replace("P","",$eventid)*1; ?>/eventid_<?php echo str_replace("P","",$eventid)*1; ?>_pic3.jpg" id="eventphoto" height="160" /><!--<img src="/sp/common2/img/book/sl03.jpg" alt="image">--></div>
            </div>
            <div class="slider-thumb">
              <div class="wrap">
                <div class="slider-nav01">
<?php
    if($picture1thumbpath != null){
?>
<div class="item"><img src="<?=IMG_URL.$picture1thumbpath;?>" alt="<?=$picture1caption;?>" height="60" /><?php echo $picture1caption; ?></div>
<?php
    }
    if($picture2thumbpath != null){
?>
<div class="item"><img src="<?=IMG_URL.$picture2thumbpath;?>" alt="<?=$picture2caption;?>" height="60" /><?php echo $picture2caption; ?></div>
<?php
    }
    if($picture3thumbpath != null){
?>
<div class="item"><img src="<?=IMG_URL.$picture3thumbpath;?>" alt="<?=$picture3caption;?>" height="60" /><?php echo $picture3caption; ?></div>
<?php
    }
?>
                </div>
              </div>
            </div>
          </div>




<?php /*          <p class="article-caption">【目黒川桜回廊クルーズ＆隅田川千本桜クルーズ】都内最大級の2つの桜回廊をお花見！</p> */ ?>
        </section>
        <section class="basic-info">
          <h2 class="headline01">ツアーの基本情報</h2>
          <div class="wrapper">
            <table class="tbl-box">
              <tr>
                <th class="th">集合場所</th>
                <td class="td">
<!--				<div class="mb15"></div>-->
				<?php 
				if($maplink == ""):
					echo $place; 
				else:
				?>
                <a href="<?php echo $maplink; ?>"><?php echo $place; ?></a>
                <?php
				endif;
				?></td>
              </tr>
              <tr>
                <th class="th">開催時間</th>
                <td class="td"><?=$starttime;?><?=$lbl->printL('kara')?><?=$endtime;?></td>
              </tr>
              <tr>
                <th class="th">料金</th>
                <td class="td"><strong class="price"><?=$adultfee;?></strong><?=$optionsetumei;?></td>
              </tr>
              <tr>
                <th class="th">テーマ</th>
                <td class="td">
				<?php
				if( count($themes) > 0):
					foreach($themes as $theme):
				?>
				<a href="/sp/book/eventlist.php?theme_id[]=<?php echo $theme['themeid']; ?>"><?php echo $theme['name']; ?></a>　
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
				if( count($purposes) > 0):
					foreach($purposes as $purpose):
				?>
                <a href="/sp/<?php echo str_replace("_","/",$purpose['purposeid']); ?>/"><?php echo $purpose['name']; ?></a>　
				<?php /*<a href="/sp/book/eventlist.php?purpose_id[]=<?php echo $purpose['purposeid']; ?>"><?php echo $purpose['name']; ?></a>　*/ ?>
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
				if( count($areas) > 0):
					foreach($areas as $area):
				?>
				<a href="/sp/book/eventlist.php?area_id[]=<?php echo $area['areaid']; ?>"><?php echo $area['name']; ?></a>　
				<?php
					endforeach;
				endif;
				?>
				
				<?php #echo $themeText; ?></td>
              </tr>
<?php /*
              <tr>
                <th class="th">口コミ</th>
                <td class="td"><p class="rate01"><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><small>（<a href="#">XX件</a>）</small></p></td>
              </tr>
*/ ?>

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
<td class="td">

<div class="guide_eventlist_wrap">
<div class="guide_eventlist_l">

	<div class='guide_eventlist_prof'><a href='/guide-member/profile/?guide_id=<?php echo $guide['id'];?>'><img src='<?php echo IMAGE_BASE.$guide['image_profile']; ?>' alt='<?php echo $guide['sei']." ".$guide['mei']; ?>'></a></div>

</div>
<div class="guide_eventlist_r">

	<div class="guide_eventlist_name"><?php echo $guide['sei']." ".$guide['mei']; ?></div>
    <div class='guide_eventlist_eval'><span class='rate rate<?php echo $eval_int.$eval_decimals;?>'></span><a href='/guide-member/profile/?guide_id=<?php echo $guide['id'];?>'>(<?php echo $eval_int.".".$eval_decimals;?>)</a></div>
    <div class="guide_eventlist_eventdate">
    <div class="c_cc0000">『開催日<?php echo $guide['event_date'];
	if($guide['thirdid']):
		echo " (".$guide['thirdid'].")";
	endif;?>
	』 を担当します</div>
ガイド歴:<?php echo $guide['guide_history']; ?>
	</div>
    

</div>
</div>

</td>
</tr>
<?php endif; ?>

            </table>
            <div class="btn-group clearfix">
              <p><a class="btn-style" href="#" onclick="openDropdown(1);">申し込みへ</a></p>
              <p><a class="btn-style" href="#" onclick="openDropdown(0);">ツアー詳細へ</a></p>
            </div>
            <a name="link0"></a>
            <div class="basic-block">
              <div class="ul-info-block">
                <div class="li">
                  <div class="item">
                    <a class="dropdown" href="#dropdown">おすすめポイント</a>
                    <div class="accordion">
                      <div class="list-desc-detail">
                        <div class="desc-detail">

<?php echo $eventcontents;?>
<?php echo $eventsubstance; ?>

<?php /*    
                          <div class="desc-item">
                            <h3 class="title circle">目黒川桜回廊貸切クルーズ＆隅田川千本桜クルーズ 2大桜名所めぐり</h3>
                            <div class="desc">
                              <p>「浜松町集合・バス送迎」都内最大級の2つの桜回廊をお花見クルーズ！</p>
                              <p>1日で2つの大人気クルーズを満喫！ポケカルおすすめ「お花見航路」</p>
                            </div>
                          </div>
                          <div class="desc-item">
                            <h3 class="title circle">圧巻！船から見上げる「目黒川桜回廊貸切クルーズ」</h3>
                            <p class="item-txt01">都内最大級の桜回廊！<br>約3.8kmの桜並木が目黒川を覆うように両岸から咲き誇ります！<br>水上の特等席より見上げる「桜のトンネル」をお楽しみください。</p>
                            <p class="item-txt02">・目黒川桜回廊貸切クルーズの予定航路<br>浜松町集合場所 ⇒ 天王洲 or 北品川乗船場までバス送迎<br>天王洲 ⇒ 昭和橋 ⇒ 御成橋 ⇒ 五反田大橋 ⇒ 市場橋 ⇒ 太鼓橋付近にて折り返し ⇒ 乗船場帰港</p>
                            <ul class="note-list fs">
                              <li>※太鼓橋（目黒雅叙園）付近にて折り返しいたします。（クルーズ時間 約70分予定）</li>
                              <li>※航路は、当日の状況や天候状況等により変更になる場合がございます。</li>
                            </ul>
                          </div>
                          <div class="desc-item">
                            <h3 class="title circle">隅田川の両岸を覆う千本桜堤へ「隅田川千本桜クルーズ」</h3>
                            <p class="item-txt01">東京の桜景色の代名詞！<br>隅田川の両岸に咲く「隅田川千本桜（江戸時代から続く1,000本の桜並木）」をお楽しみください！</p>
                            <p class="item-txt02">・隅田川千本桜クルーズの予定航路<br>日の出乗船場 ⇒ 勝鬨橋 ⇒ 佃大橋 ⇒ 永代橋 ⇒ 清洲橋 ⇒ 両国橋 ⇒ 厩橋 ⇒ 吾妻橋 ⇒ 浅草乗船場</p>
                            <ul class="note-list fs">
                              <li>※航路は、当日の状況や天候状況等により変更になる場合がございます。</li>
                            </ul>
                          </div>

                        <div class="desc-detail">
                          <div class="desc-item">
                            <h3 class="title">【料金】</h3>
                            <p class="desc">大人：平日 ￥7,980、土日 ￥8,480<br>子供（小学生）：平日・土日 ￥6,980
                            </p>
                            <ul class="note-list fs">
                              <li>※未就学児童は参加出来ません。</li>
                            </ul>
                          </div>
                        </div>
                        <div class="desc-detail">
                          <div class="desc-item">
                            <h3 class="title">【ご確認事項】</h3>
                            <ul class="list-txt-style">
                              <li>・桜の開花状況に関わらず、開催決定後は、ツアーを開催いたします。</li>
                              <li>・子供料金（小学生）となります。未就学児童は参加出来ません。</li>
                              <li>・桜の開花状況に関わらず、開催決定後は荒天の場合を除き開催いたします。</li>
                              <li>・雨天も開催いたします。雨天時には各自にて傘以外の雨具（レインコートなど）の準備をお願いいたします。</li>
                              <li>・当ツアーにはお食事が付きません。軽食程度（おにぎり等）なら、お客様の膝下にて各船内でお召し上がりいただけます。その際のゴミはお 持ち帰りいただきます。</li>
                              <li>・目黒川クルーズ＆隅田川クルーズに関するお問い合わせは「ポケカル」にお問い合わせください。</li>
                            </ul>
                          </div>
                          <div class="desc-item desc-item01">
                            <h3 class="title">【荒天等により桜クルーズが中止の場合】</h3>
                            <ul class="list-txt-style">
                              <li>・目黒川桜クルーズが中止の場合は、参加費より￥2,000をご返金いたします。その際「東京タワー大展望台見学」に変更し隅田川クルーズへご案内いたします。</li>
                              <li>・目黒川桜クルーズ・隅田川桜クルーズの2つのクルーズが中止の場合は参加費全額を払い戻しいたします。</li>
                            </ul>
                            <ul class="note-list fs">
                              <li>※ご返金に関しては、後日、ポケカルよりご連絡をいたします。</li>
                            </ul>
                          </div>

                        </div>
*/ ?>

                      </div>
                    </div>
                  </div>
                </div>
                <div class="li">
                  <div class="item">
                    <a class="dropdown" href="#dropdown" name="link1">出発日カレンダー</a>
                    <div class="accordion wrapper">
                      
<!--                      <p class="thumb"><img src="/sp/common2/img/book/thumb.jpg" alt="thumb"></p>-->
                      <?php echo $calendar_html; ?>
<!--                      <p class="booking"><a class="btn-style" href="tel:0356527072">お電話でもご予約可能です　03-5652-7072</a></p>-->
                    </div>
                  </div>
                </div>
                <div class="li">
                  <div class="item">
                    <a class="dropdown" href="#dropdown">行程スケジュール</a>
                    <div class="accordion wrapper">
                    
                    <?php echo $schedule_html; ?>
<?php /*
                      <dl class="schedule-time">
                        <dt class="dt">09:00</dt>
                        <dd class="dd">（JR浜松町駅北口）【汐留芝離宮ビルディング前】広場付近にて集合・受付け</dd>
                        <dt class="dt">09:30</dt>
                        <dd class="dd">【目黒川桜回廊クルーズ】（※予定時間　9:30〜10:40）</dd>
                        <dt class="dt">11:40</dt>
                        <dd class="dd">【隅田川千本桜クルーズ】（※予定時間　11:40〜12:30）</dd>
                        <dt class="dt">12:30</dt>
                        <dd class="dd">【浅草・吾妻橋乗船場】到着後、終了となります。</dd>
                      </dl>
                      <ul class="note-list">
                        <li>※当日の状況によりスケジュールが変更になる場合がございます。添乗員の指示に従ってください。</li>
                        <li>※【集合場所】から【各乗船場】までは、バス送迎でご案内いたします！</li>
                      </ul>
                      <p class="booking"><a class="btn-style" href="tel:0356527072">お電話でもご予約可能です　03-56527072</a></p>
*/ ?>
                    </div>
                  </div>
                </div>
                <div class="li">
                  <div class="item">
                    <a class="dropdown" href="#dropdown">ご確認・注意事項</a>
                    <div class="accordion wrapper">
                      <dl class="condition">
                        <dt class="dt">【最小催行人員】</dt>
                        <dd class="dd"><?=$minsaikounum;?></dd>
                        <dt class="dt">【受付可能日】</dt>
                        <dd class="dd"><?=$uketukekanoubi;?></dd>
                        <dt class="dt">【キャンセル規定】</dt>
                        <dd class="dd last">
                          <p>
                          <a name="cancelrule"></a>[<?=$lbl->printL('kyanserukitei')?>]
							<?=$cancelrule;?>
                          </p>
<?php /*
                          <ul class="note-list">
                            <li>※取消日は、お客様が当社の営業日・営業時間内にお申し出いただいたときを基準とします。</li>
                            <li>※旅行契約の成立後、上記取消日区分に入ってからの人員減、旅行開始日・コースの変更は取消とみなされ、取消料がかかります。詳しくは、こちらをご確認ください。</li>
                          </ul>
*/ ?>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="vote-bl">
<?php /*              <a class="like" href="#"><i class="fa fa-heart"></i>行きたい！</a><span class="amount">2114</span> */ ?>
              <a class="favorite01" href="javascript:void(0)" onclick="setFavorite('<?php echo $eventid4history ; ?>');return false;"><img src="/sp/common2/img/common/favorite.png" alt="favorite">お気に入りリストに追加</a>
            </div>
          </div>
        </section>

<?php	
$cookie_name = "mrraf";
$asid = isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : "";
$txt = "";
if($asid == 1201):
?>

	<div class="mb40"></div>

<?php else: ?>

        <section class="contact-us">
          <h2 class="headline01">お問い合わせ</h2>
          <div class="contact-content">
            <p class="txt01">ツアーのご予約はお電話でも受付可能です。</p>
            <p class="tel"><a href="tel:0356527072"><img src="/sp/common2/img/book/number_phone.png" alt="number phone" class="inline-block"></a></p>
            <p class="time-contact">
              <span class="title">営業時間</span>
              <span class="desc-time">ポケカルお客様センター 9:00〜18:00 年中無休</span>
            </p>
            <p>お問い合わせ・ご予約の際、下記の商品番号をお伝えいただくとスムーズです。</p>
            <p class="item-number"><span>商品番号：<?= $eventid; ?></span></p>
            <h3 class="title-contact">インターネットでのご予約は24時間365日受付ております。</h3>
            <div class="detail-contact">
              <h4 class="title">ご確認・ご注意事項</h4>
              <p>※「受付終了」の場合もご案内可能なご日程がある場合がございますので、お客様センターへお問い合せください。 キャンセル・変更については、お客様センターまでお電話にてお問い合せください。（<a href="#">キャンセル規約はこちら</a>）</p>
              <p>当社の個人情報の取り扱いに関する詳細はこちらの 「<a href="/sp/privacy.html">個人情報保護方針</a>」をご覧ください。</p>
            </div>
          </div>
        </section>

        <div class="menu-site menu-site-bl">
          <div class="wrapper">
            <div class="inner-ct">
              <h2 class="headline02">テーマで探す</h2>
              <ul class="ul-menu-block clearfix">
              <?php echo $data['theme_sp_navi']; ?>
<?php /*
                <li><a href="#"><span>話題の観光地・スポット</span></a></li>
                <li><a href="#"><span>迎賓館見学</span></a></li>
                <li><a href="#"><span>クルーズ</span></a></li>
                <li><a href="#"><span>花・自然・絶景</span></a></li>
                <li><a href="#"><span>散策・街歩き</span></a></li>
                <li><a href="#"><span>工場・社会科見学</span></a></li>
                <li><a href="#"><span>絶品グルメ・食べ放題</span></a></li>
                <li><a href="#"><span>寺社めぐり</span></a></li>
                <li><a href="#"><span>伝統芸能・祭り</span></a></li>
                <li><a href="#"><span>絵画・美術展・イベント</span></a></li>
                <li><a href="#"><span>エンタメ・ショー・ライブ</span></a></li>
                <li><a href="#"><span>京都日帰りツアー・プラン</span></a></li>
                <li><a href="#"><span>体験・学習</span></a></li>
                <li><a href="#"><span>レストランプラン</span></a></li>
                <li><a href="#"><span>添乗員なし</span></a></li>
                <li><a href="#"><span>日帰り温泉</span></a></li>
                <li><a href="#"><span>屋形船</span></a></li>
                <li><a href="#"><span>花火観覧</span></a></li>
                <li><a href="#"><span>大人合コン</span></a></li>
                <li><a href="#"><span>大阪・神戸日帰りツアー・プラン</span></a></li>
                <li><a href="#"><span>九州日帰りツアー・プラン</span></a></li>
                <li><a href="#"><span>その他</span></a></li>
                <li><a href="#"><span>新聞掲載ツアー</span></a></li>
*/ ?>
                <li><a href="/sp/all-themes/"><span>全てのテーマを見る</span></a></li>
              </ul>
            </div>
          </div>
          <div class="wrapper">
            <div class="inner-ct last">
              <h2 class="headline02">チケットを探す</h2>
              <ul class="ul-menu-block clearfix">
              
              <?php echo $data['ticket_sp_navi']; ?>
<?php /*
                <li><a href="#"><span>演劇</span></a></li>
                <li><a href="#"><span>コンサート・ライブ</span></a></li>
                <li><a href="#"><span>ミュージカル・オペラ</span></a></li>
                <li><a href="#"><span>バレエ・ダンス</span></a></li>
                <li><a href="#"><span>歌舞伎・落語・狂言</span></a></li>
                <li><a href="#"><span>サーカス</span></a></li>
                <li><a href="#"><span>スポーツ</span></a></li>
                <li><a href="#"><span>その他</span></a></li>
*/ ?>
                <li><a href="/sp/book/eventlist.php?theme_id=ticket"><span>全てのチケットを見る</span></a></li>
              </ul>
            </div>
          </div>
        </div>

        <section class="sec-block">
          <?php #require_once("2018/popular.html"); ?>
          <?php require_once("2018/communicate.html"); ?>
          <?php #require_once("2018/news_latest.html"); ?>
          <?php #require_once("2018/favorites.html"); ?>
        </section>
        
        <?php #echo $data['favorites']; ?>
        <?php #echo $data['history']; ?>

<?php endif; ?>

<script>
function openDropdown(n){

  $(".dropdown").eq(n).addClass('active');
  $(".dropdown").eq(n).parent().find('> .accordion').show("fast");
  
  var p = $(".dropdown").eq(n).offset().top - 90;
  $('html,body').animate({ scrollTop: p }, 'slow');
  return false;

}
</script>
      <?php require_once("2018/footer.html"); ?>
