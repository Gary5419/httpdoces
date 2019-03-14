


<!-- 2012.10.11 add{ -->
<!-- Google Code for &#12522;&#12510;&#12540;&#12465;&#12486;&#12451;&#12531;&#12464; &#12479;&#12464; -->
<!-- Remarketing tags may not be associated with personally identifiable information or placed on pages related to sensitive categories. For instructions on adding this tag and more information on the above requirements, read the setup guide: google.com/ads/remarketingsetup -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1001285664;
var google_conversion_label = "HuyMCJiqxgMQoNC53QM";
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/1001285664/?value=0&amp;label=HuyMCJiqxgMQoNC53QM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- }2012.10.11 add -->

<!-- rttag YDN 20130507 START -->
<script type="text/javascript" language="javascript">
var yahoo_retargeting_id = 'listing1889';
var yahoo_retargeting_label = '';
</script>
<script type="text/javascript" language="javascript" src="//b92.yahoo.co.jp/js/s_retargeting.js"></script>
<!-- rttag YDN 20130507 END -->

<?php
if($_SERVER['SERVER_PORT'] == 443){
/************************************************************************/
/* HTTPSページ用タグ表示                                                 */
/************************************************************************/
/* HTTPS_PAGEの間に記述してください。 */
print <<< HTTPS_PAGE

<!-- BLADE用リタゲタグ -->
<script type="text/javascript">
<!--
var blade_co_account_id='2019';
var blade_group_id='';
-->
</script>
<script src="https://d-track.send.microad.jp/js/bl_track.js">
</script>
<!-- /BLADE用リタゲタグ -->

HTTPS_PAGE;
}else{
/************************************************************************/
/* HTTPページ用タグ表示                                                  */
/************************************************************************/
/* HTTP_PAGEの間に記述してください。 */
print <<< HTTP_PAGE

<!-- BLADE用リタゲタグ -->
<script type="text/javascript">
<!--
var blade_co_account_id='2019';
var blade_group_id='';
-->
</script>
<script src="http://d-cache.microad.jp/js/bl_track.js">
</script>
<!-- /BLADE用リタゲタグ -->

<!-- 電話カウント用タグ -->
<!-- Google Code for &#12473;&#12510;&#12507;&#38651;&#35441;CV&#12304;2013.7&#65374;&#12305; Conversion Page
In your html page, add the snippet and call
goog_report_conversion when someone clicks on the
phone number link or button. -->
<script type="text/javascript">
  /* <![CDATA[ */
  goog_snippet_vars = function() {
    var w = window;
    w.google_conversion_id = 1004406842;
    w.google_conversion_label = "xBxQCN6XwAcQupD43gM";
    w.google_conversion_value = 0;
  }
  // DO NOT CHANGE THE CODE BELOW.
  goog_report_conversion = function(url) {
    goog_snippet_vars();
    window.google_conversion_format = "3";
    window.google_is_call = true;
    var opt = new Object();
    opt.onload_callback = function() {
    if (typeof(url) != 'undefined') {
      window.location = url;
    }
  }
  var conv_handler = window['google_trackConversion'];
  if (typeof(conv_handler) == 'function') {
    conv_handler(opt);
  }
}
/* ]]> */
</script>
<script type="text/javascript"
  src="http://www.googleadservices.com/pagead/conversion_async.js">
</script>
<!-- /電話カウント用タグ -->

HTTP_PAGE;
}
if(!ereg("end\.php", $_SERVER["REQUEST_URI"])){
/************************************************************************/
/* 申込完了(サンクス)ページ以外用タグ表示                                  */
/************************************************************************/
/* NOTEND_PAGEの間に記述してください。 */
print <<< NOTEND_PAGE

<!-- フリーセルリマケ用タグ 2013.10.21 -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 979400865;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/979400865/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- /フリーセルリマケ用タグ 2013.10.21 -->

<!-- BLADE 計測タグ -->
<script type="text/javascript" class="microad_blade_track">
<!--
var microad_blade_jp = microad_blade_jp || { 'params' : new Array(), 'complete_map' : new Object() };
(function() {
var param = {'co_account_id' : '6959', 'group_id' : '', 'country_id' : '1', 'ver' : '2.1.0'};
microad_blade_jp.params.push(param);

var src = (location.protocol == 'https:')
? 'https://d-track.send.microad.jp/js/blade_track_jp.js' : 'http://d-cache.microad.jp/js/blade_track_jp.js';

var bs = document.createElement('script');
bs.type = 'text/javascript'; bs.async = true;
bs.charset = 'utf-8'; bs.src = src;

var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(bs, s);
})();
-->
</script>
<!-- /BLADE 計測タグ -->

NOTEND_PAGE;
}else{
/************************************************************************/
/* 申込完了(サンクス)ページ用タグ表示                                  */
/************************************************************************/
/* END_PAGEの間に記述してください。 */
print <<< END_PAGE

<!-- YahooリスティングCVタグ 20150127 -->
<!-- Yahoo Code for your Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var yahoo_conversion_id = 1000178378;
var yahoo_conversion_label = "QkoDCOuNmVkQu-6KyAM";
var yahoo_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="https://s.yimg.jp/images/listing/tool/cv/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://b91.yahoo.co.jp/pagead/conversion/1000178378/?value=0&amp;label=QkoDCOuNmVkQu-6KyAM&amp;guid=ON&amp;script=0&amp;disvt=true"/>
</div>
</noscript>
<!-- /YahooリスティングCVタグ 20150127 -->

<!-- GoogleリスティングCVタグ 20150127 -->
<!-- Google Code for PC_CV&#12479;&#12464; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 953475869;
var google_conversion_language = "ja";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "o7b9CPuOmVkQncbTxgM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/953475869/?label=o7b9CPuOmVkQncbTxgM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- /GoogleリスティングCVタグ 20150127 -->

<!-- YDN CVタグ　20150127 -->
<script type="text/javascript" language="javascript">
  /* <![CDATA[ */
  var yahoo_ydn_conv_io = "nlRxecoOLDWgx0Fsg8VC";
  var yahoo_ydn_conv_label = "OKNZWMLG4ZCMRFC73TM822392";
  var yahoo_ydn_conv_transaction_id = "";
  var yahoo_ydn_conv_amount = "0";
  /* ]]> */
</script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="//b90.yahoo.co.jp/conv.js"></script>
<!-- YDN CVタグ　20150127 -->


<!-- BLADE CVタグ -->
<script type="text/javascript" class="microad_blade_track">
<!--
var microad_blade_jp = microad_blade_jp || { 'params' : new Array(), 'complete_map' : new Object() };
(function() {
var param = {'co_account_id' : '6959', 'group_id' : 'convtrack19973', 'country_id' : '1', 'ver' : '2.1.0'};
microad_blade_jp.params.push(param);

var src = (location.protocol == 'https:')
? 'https://d-track.send.microad.jp/js/blade_track_jp.js' : 'http://d-cache.microad.jp/js/blade_track_jp.js';

var bs = document.createElement('script');
bs.type = 'text/javascript'; bs.async = true;
bs.charset = 'utf-8'; bs.src = src;

var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(bs, s);
})();
-->
</script>
<!-- /BLADE CVタグ -->

<!-- BLADE ディスプレイ広告 CVタグ -->
<!-- Google Code for &#30003;&#12375;&#36796;&#12415; Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 976836089;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "MtBiCJ_tzxoQ-avl0QM";
var google_conversion_value = 0;
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/976836089/?value=0&amp;label=MtBiCJ_tzxoQ-avl0QM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- /BLADE ディスプレイ広告 CVタグ -->

<!-- システムソフト　CVタグ　2015.9.3 -->
<script type="text/javascript" language="javascript">
  /* <![CDATA[ */
  var yahoo_ydn_conv_io = "nlRxecoOLDWgx0Fsg8VC";
  var yahoo_ydn_conv_label = "OKNZWMLG4ZCMRFC73TM822392";
  var yahoo_ydn_conv_transaction_id = "";
  var yahoo_ydn_conv_amount = "0";
  /* ]]> */
</script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="//b90.yahoo.co.jp/conv.js"></script>
<!-- /システムソフト　CVタグ　2015.9.3 -->

END_PAGE;
}
?>
