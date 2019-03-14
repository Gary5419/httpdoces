<?php
/**
 * クチコミ完了画面
 *
 *
 */
//ini_set( 'display_errors', 'On' );

ini_set('session.use_only_cookies', 1);
session_cache_limiter('private_no_expire');
session_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja" xml:lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>クチコミ投稿完了|ぽけかる倶楽部</title>
    <meta name="description" content="クチコミ情報が満載!ポケットカルチャー。ツアー予約ならぽけかる倶楽部へ。クチコミもぜひご覧ください" />
    <meta name="keywords" content="クチコミ投稿完了,日帰りツアー,体験イベント,現地集合,現地解散,ぽけかる倶楽部" />
    <meta name="robots" content="noydir" />
    <meta name="robots" content="noodp" />
    <meta content="text/javascript" http-equiv="Content-Script-Type" />
    <meta content="text/css" http-equiv="Content-Style-Type" />
    <link href="/common/css/base.css" type="text/css" rel="stylesheet" />
    <link href="/common/css/errstyle.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="/common/js/heightLine.js"></script>
    <script type="text/javascript" src="/common/js/valueClear.js"></script>
    <script type="text/javascript" src="/common/js/pageTop.js"></script>
    <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
<body id="index">
<?php include_once("/_data/tags/body_tag.php"); ?>
<div id="wrapper">

<h1>クチコミ投稿完了｜現地集合・現地解散型 お手軽日帰りツアー・体験イベント</h1>

<div id="header">
<div id="site-title"><a href="/"><img src="/common/img/sitetitle.gif" alt="ぽけかる倶楽部 " /></a></div>
<ul id="header-navi">
<li><a href="/tokutyo/index.html">ぽけかる倶楽部について</a></li>
<li><a href="/faq/index.html">よくある質問と回答</a></li>
<li><a href="/howto/index.html">お申込方法</a></li>
<li><a href="/sitemap.html">サイトマップ</a></li>
</ul>
</div><!-- //#header -->

<ul id="droplink">
<li><strong><b><a href="/">日帰りツアー・体験イベント トップ</a></b></strong>&nbsp;&nbsp;>&nbsp;クチコミ登録</li>
<li id="catch"><b><strong>日帰りツアー・体験イベント</strong></b></li>
</ul><!-- //#droplink -->

<div id="container">

<div id="inquiry">

<div id="comment">ご意見・ご感想を頂きありがとうございました。
    <p><br />今後とも楽しいツアーやイベントを提供させて頂きます。<br />
	ぽけかる倶楽部のまたのご利用をこころからお待ちしております。<br />
	※投稿されたクチコミは承認後、公開せていただきます。</p><br />
    <a href="/book/calendar.php?eventid=<?=htmlspecialchars($_SESSION['kuchikomi_event_id'], ENT_QUOTES)?>">元のイベント詳細ページ</a>に戻る</div>

</div><!-- //#inquiry-->

</div><!-- //#container -->

<div id="footer">


<div id="pageup"><a href="javascript:void(0);" onclick="pageTop(); return false;" onkeypress="0"><img src="/common/img/pageup.gif" alt="ページトップへ ▲" /></a></div>

<div id="common">
<div id="copyright">Copyright c 2009 Pokekaru-Club. All rights reserved.</div>
<ul>
<li><a href="/company.html">運営会社</a>&nbsp;｜&nbsp;</li>
<li><a href="/ryokogyo.html">旅行業登録票</a>&nbsp;｜&nbsp;</li>
<li><a href="/privacy.html">個人情報保護</a>&nbsp;｜&nbsp;</li>
<li><a href="/copyright.html">著作権</a>&nbsp;｜&nbsp;</li>
<li><a href="/yakkan.html">約款</a>&nbsp;｜&nbsp;</li>
<li><a href="/book/inquiry.php">お問い合わせ</a></li>
</ul>
</div>

</div><!-- //#footer -->

</div><!-- //#wrapper -->


</body>
</html>