
<?php
if($_SERVER['SERVER_PORT'] == 443){
/************************************************************************/
/* HTTPSページ用タグ表示                                                 */
/************************************************************************/
/* HTTPS_PAGEの間に記述してください。 */
print <<< HTTPS_PAGE


HTTPS_PAGE;
}else{
/************************************************************************/
/* HTTPページ用タグ表示                                                  */
/************************************************************************/
/* HTTP_PAGEの間に記述してください。 */
print <<< HTTP_PAGE

<!-- for pixel tracking -->
<img src="//m.one.impact-ad.jp/pix?p=19575&t=i"/>

HTTP_PAGE;
}
if(!ereg("end\.php", $_SERVER["REQUEST_URI"])){
/************************************************************************/
/* 申込完了(サンクス)ページ以外用タグ表示                                  */
/************************************************************************/
/* NOTEND_PAGEの間に記述してください。 */
print <<< NOTEND_PAGE



NOTEND_PAGE;
}else{
/************************************************************************/
/* 申込完了(サンクス)ページ用タグ表示                                  */
/************************************************************************/
/* END_PAGEの間に記述してください。 */
print <<< END_PAGE

<!-- CVタグ　2015.9.3 -->
<script type="text/javascript" language="javascript">
  /* <![CDATA[ */
  var yahoo_ydn_conv_io = "nlRxecoOLDWgx0Fsg8VC";
  var yahoo_ydn_conv_label = "OKNZWMLG4ZCMRFC73TM822392";
  var yahoo_ydn_conv_transaction_id = "";
  var yahoo_ydn_conv_amount = "0";
  /* ]]> */
</script>
<script type="text/javascript" language="javascript" charset="UTF-8" src="//b90.yahoo.co.jp/conv.js"></script>
<!-- /CVタグ　2015.9.3 -->

END_PAGE;
}
?>

<!--  end/YiCHA 共通タグ　20150302 -->


