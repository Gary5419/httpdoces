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

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />

<title>口コミ登録完了｜ぽけかる倶楽部</title>

<meta name="viewport" content="width=320">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="/sp/common/js/google-analytics.js"></script>
<script src="/sp/common/js/share.js"></script>
<script src="/sp/common/js/script.js"></script>

<meta name="format-detection" content="telephone=no" />

<!--<link rel="apple-touch-icon" href="./img/apple-touch-icon.png" />
<link rel="shortcut icon" href="./favicon.ico" />-->

<link rel="stylesheet" href="/sp/common/css/import.css" />
    <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
<body id="kuchikomi">
<?php include_once("/_data/tags/body_tag.php"); ?>
<div id="page">

<?php include("header.inc"); ?>

<section class="headline">
<h1>口コミ登録</h1>
<ul>
<li><a href="/sp/">TOP</a></li>
<li>口コミ登録</li>
<li>登録内容確認</li>
<li>登録完了</li>
</ul>
</section><!-- / .headline -->

<div id="top">

<p class="f-16 f-b">ご意見・ご感想を頂きありがとうございました。</p>

<p class="f-16">今後とも楽しいツアーやイベントを提供させて頂きます。
ぽけかる倶楽部のまたのご利用をこころからお待ちしております。</p>

<p>※投稿されたクチコミは承認後、公開させていただきます。</p>
</div><!-- /#top -->


<div class="preview">
<p class="t-a-c m-b-15"><a href="/sp/"><img src="/sp/common/img/btn_totop2.png" width="271" height="58" alt="トップページへ"></a>
</p>
</div>

<div class="bottom">

<p class="btn"><a href="#page"><img src="/sp/common/img/bottom_point.png" width="18" height="20">ページの先頭へ戻る</a></p>

</div><!-- / .bottom -->


<?php include("footer_nopclink.inc"); ?>

</div>
<!-- / #page -->

</body>
</html>