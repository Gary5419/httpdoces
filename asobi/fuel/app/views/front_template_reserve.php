<?php
#$domain_mypage = "mypage.poke.co.jp";
$domain_mypage = MYPAGE_HOST;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="バスツアーやクルーズ、体験イベントなどの日帰り旅行を予約するならポケカルへ。東京観光、女子旅、添乗員なしの気軽な一人旅プランなどなんでもお任せください！" />
<meta name="keywords" content="日帰りバスツアー,日帰り,バスツアー,旅行,日帰りツアー,ツアー,クルーズ,女子旅,東京観光,ポケカル" />
<meta name="_globalsign-domain-verification" content="uF3MRzfyoKNYrpQ1mReq8_tpTHOItX5gmpd6CMFcfT" />
<title>日帰りバスツアーや旅行の予約ならポケカルへ</title>
<link rel="stylesheet" type="text/css" href="/asobi/css/style.css?v=20170704" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <?php include_once("/_data/tags/head_tag.php"); ?>
</head>

<body>
<?php include_once("/_data/tags/body_tag.php"); ?>
<!-- ヘッダーここから -->
<div id="headline">
    <h1 id="headh1">日帰り旅行やバスツアーの予約ならポケカルへ&nbsp;</h1>
  </div>
  <div id="head">
    <div id="head_left"><a href="http://www.poke.co.jp/"><div id="head_logo" style="margin:17px 0 0 10px;"><img src="/images/top/logo_170101.png" alt="日帰りのツアーや見学や体験イベントに出掛けるならポケカルへ♪" width="217" id="imgLogo" /></div></a></div>

    <div id="head_right">

<iframe width="320" height="65" src="<?php echo $domain_mypage; ?>/_checklogin.php?redirect=<?php echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>" frameborder="0" scrolling="no" id="ckecklogin"></iframe>

      <ul id="snavi">
      <li><a href="http://www.poke.co.jp/sitemap.html">サイトマップ</a></li>
            <li><a href="http://www.poke.co.jp/faq/index.html">よくある質問</a></li>
            <li><a href="http://www.poke.co.jp/howto/index.html">お申込み方法</a></li>
          <li><a href="http://www.poke.co.jp/tokutyo/index.html">ポケカルとは？</a></li>
        </ul>

    </div>
  </div>
<!-- ヘッダーここまで -->

<div id="wrap">

<?php echo $content; ?>


</div>

<div class="mb100"></div>

<div id="footer">

	<div id="footnav">
    <a href="http://www.poke.co.jp/company.html">運営会社</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/media.html">メディア掲載実績</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/pr.html">プレスリリース</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/ryokogyo.html">旅行業登録票</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/privacy.html">個人情報保護</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/copyright.html">著作権</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/yakkan.html">約款</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/tuuhan.html">通販規約</a>&nbsp;｜&nbsp;<a href="http://www.poke.co.jp/book/inquiry.php">お問い合わせ</a>
    </div>
    
    
    <p>© 2018 POKEKARU inc.</p>

</div>
</body>
</html>

<?php /*

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo SITE_NAME; ?> - <?php echo $title ?></title>
        <!--[if IE]>
            <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <meta name="description" content="" />
        <meta name="keywords" content="<?php echo SITE_ENGLISH_NAME; ?>" />
        <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <?php echo Asset::css("jquery-ui.css"); ?>
        <?php echo Asset::css("custom.css"); ?>
    </head>
    <body id="page_<?php echo $controller; ?>_<?php echo $action; ?>">
        <?php //echo $header ?>
        <section id="contents">
            <div>
                <!-- <div id="contents-title">
                    <h2 class="col-xs-12"><?php echo $title ?></h2>
                </div> -->
                <div id="contents-body">
                    <?php if(isset($errors)): ?>
                        <div class="bg-danger">
                            <?php foreach($errors as $info_message): ?>
                                <p class="text-danger"><?php echo $info_message; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($infos)): ?>
                        <div class="bg-info">
                            <?php foreach($infos as $info_message): ?>
                                <p class="text-info"><?php echo $info_message; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($warnings)): ?>
                        <div class="bg-warning">
                            <?php foreach($warnings as $info_message): ?>
                                <p class="text-warning"><?php echo $info_message; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php echo $content; ?>
                </div>
            </div>
        </section>
        <?php echo $footer; ?>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
        <!-- jQuery (datepicker plugins) -->
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <!-- Custom JS -->
        <script type="text/javascript" src="https://nrd-naxis.com/assets/js/custom.js?1471328642"></script>
        <?php echo Security::js_fetch_token(); ?>
        <?php echo Form_Ex::hidden("page_url", Uri::current(), array("id" => "page_url")) ?>
        <?php if($modal_flag): ?>
            <?php foreach($modals as $modal): ?>
                <?php echo $modal; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php foreach($js_template as $template): ?>
            <?php echo $template; ?>
        <?php endforeach; ?>
    </body>
</html>
*/ ?>