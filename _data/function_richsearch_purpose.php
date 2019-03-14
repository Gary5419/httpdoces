<?php
#ini_set("display_errors",1);
#require_once("include/2018/config.php");	#リニューアル時追加
require_once 'MDB2.php';


require_once 'include/const.inc.php';
require_once 'include/common.inc.php';
require_once 'include/pokebook.inc.php';

#ini_set("display_errors",1);
$special_title = $master[0];
$column_pc = $master[1];
$column_sp = $master[2];
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


require_once dirname(dirname(__FILE__)) . '/_data/simple_html_dom.php';
#$list = getCrawlData($word);
function getCrawlData($keyword){
	$data = array();

    if($keyword != ""){
        #$url = "http://www.poke.co.jp/book/eventlist_special.php?special=hot-base&sort=new&pagenum=1&keyword={$keyword}";
		$url = "http://www.poke.co.jp/book/eventlist.php?sort=new&pagenum=1&keyword={$keyword}";
        
        $html = file_get_html($url);
        $e = $html->find('div#mainField',0);
        $ret = $e->find('div.search_results');
        
        $cnt = 0;
        $data = array();
        foreach($ret as $v){
            $data[$cnt]['title'] = $v->find('div.ttlHeader h3',0)->innertext;
            
            $summary = $v->find('div.summary p',0)->innertext;
            $summaryArr = explode("&nbsp;",$summary);
            $data[$cnt]['summary_caption'] = $summaryArr[0];
            $data[$cnt]['summary'] = $summaryArr[1];
            $obj1 = $v->find('div.retumb',0);
            $link = $obj1->find('a',0);
            $img = $obj1->find('a img',0);
            
            $data[$cnt]['link'] = $link->href;
			$data[$cnt]['alt'] = $img->alt;
            $data[$cnt]['img'] = $img->src;
			$term = $v->find('table.sctbl2',0)->find('tr',0)->find('td',1);
			$area = $v->find('table.sctbl2',0)->find('tr',1)->find('td',1);
            $priceBase = $v->find('table.sctbl2',0)->find('tr',2)->find('td',1);
			$price = $priceBase->innertext;
			$priceArr = explode("円",str_replace("&nbsp;","",$price));
            $data[$cnt]['term'] = $term->innertext;
			$data[$cnt]['area'] = $area->innertext;
			$data[$cnt]['price'] = "<em>".$priceArr[0]."</em>円".$priceArr[1];
            $cnt++;
        }
    }
	
	$list = array();
	foreach($data as $v){
		$list[str_replace("http://www.poke.co.jp/book/calendar.php?eventid=","",$v['link'])] = $v;
	}

    return $list;

}

#$params["keyword"] = $word;
$params["purposeid"] = $purposeid;
$list = getSearchData($params);

function getSearchData($params){
	$url_currentdir = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
	
if (isset($params['keyword']) and !cmIsEmpty($params['keyword'])) {
  if ("none" == $params['keyword']) {
    $p_keyword = null;
    $k_keyword = "none";
  } else {
    $p_keyword = urldecode($params['keyword']);
    $k_keyword = urlencode($params['keyword']);
  }
} else {
  $p_keyword = null;
  $k_keyword = "none";
}
if (isset($params['purposeid']) and !cmIsEmpty($params['purposeid'])) {
  if ("none" == $params['purposeid']) {
    $p_purposeid = null;
    $k_purposeid = "none";
  } else {
    $p_purposeid = $params['purposeid'];
    $k_purposeid = $params['purposeid'];
  }
} else {
  $p_purposeid = null;
  $k_purposeid = "none";
}
if (isset($params['themaid']) and !cmIsEmpty($params['themaid'])) {
  if ("none" == $params['themaid']) {
    $p_themaid = null;
    $k_themaid = "none";
  } else {
    $p_themaid = $params['themaid'];
    $k_themaid = $params['themaid'];
  }
} else {
  $p_themaid = null;
  $k_themaid = "none";
}
if (isset($params['prefid']) and !cmIsEmpty($params['prefid'])) {
  if ("none" == $params['prefid']) {
    $p_prefid = null;
    $k_prefid = "none";
  } else {
    $p_prefid = $params['prefid'];
    $k_prefid = $params['prefid'];
  }
} else {
  $p_prefid = null;
  $k_prefid = "none";
}
if (isset($params['areaid']) and !cmIsEmpty($params['areaid'])) {
  if ("none" == $params['areaid']) {
    $p_areaid = null;
    $k_areaid = "none";
  } else {
    $p_areaid = $params['areaid'];
    $k_areaid = $params['areaid'];
  }
} else {
  $p_areaid = null;
  $k_areaid = "none";
}
if (isset($params['sort']) and !cmIsEmpty($params['sort'])) {
  if ("none" == $params['sort']) {
    $p_sort = "pop";
    $k_sort = "none";
  } else {
    $p_sort = $params['sort'];
    $k_sort = $params['sort'];
  }
} else {
  $p_sort = "pop";
  $k_sort = "none";
}
if (isset($params['pagenum']) and !cmIsEmpty($params['pagenum'])) {
  if ("0" == $params['pagenum']) {
    $p_pagenum = 1;
    $k_pagenum = 0;
  } else {
    $p_pagenum = $params['pagenum'];
    $k_pagenum = $params['pagenum'];
  }
} else {
  $p_pagenum = 1;
  $k_pagenum = 0;
}
if (isset($params['y']) and !cmIsEmpty($params['y'])) {
  if ("0000" == $params['y']) {
    $p_year = null;
    $k_year = "0000";
  } else {
    $p_year = $params['y'];
    $k_year = $params['y'];
  }
} else {
  $p_year = null;
  $k_year = "0000";
}
if (isset($params['m']) and !cmIsEmpty($params['m'])) {
  if ("00" == $params['m']) {
    $p_month = null;
    $k_month = "00";
  } else {
    $p_month = $params['m'];
    $k_month = $params['m'];
  }
} else {
  $p_month = null;
  $k_month = "00";
}
if (isset($params['d']) and !cmIsEmpty($params['d'])) {
  if ("00" == $params['d']) {
    $p_day = null;
    $k_day = "00";
  } else {
    $p_day = $params['d'];
    $k_day = $params['d'];
  }
} else {
  $p_day = null;
  $k_day = "00";
}

//日付チェック
$day_search = false;
$date_chk = true;
$error_msg = null;
if ($p_year == null && $p_month == null && $p_day == null) {
  $day_search = false;
  $date_chk = true;
  $error_msg = null;
} else {
  $day_search = true;
  $date_chk = blnPrmCheckDate($p_year, $p_month, $p_day, 6);
  if (!$date_chk) {
    $minday = strftime("%Y年%m月%d日", strtotime(date("Ymd")));
    $maxday = strftime("%Y年%m月%d日", strtotime(date("Ymd", mktime(0, 0, 0, date("m") + 7, 0, date("Y")))));
    $error_msg = "日程は" . $minday . "～" . $maxday . "で指定してください。";
  }
}

// MDB2:: factory() を使用して、インスタンスの作成と、ホストへの接続を行う
$mdbwww = & MDB2:: factory(DB_DSN_WWW);
if (PEAR:: isError($mdbwww)) {
  //die($mdb2->getMessage());
  //ログの出力を行い、エラーページへ遷移
}
$mdbwww->setFetchMode(MDB2_FETCHMODE_ASSOC);

// アフェリエイト対応
$cookiedata = array();
$atid = '0';
$asid = '0';
$visitday = '';



                      if ($day_search) {
                        if ($date_chk) {
                          $saikoubi = date("Y\-m\-d", mktime(0, 0, 0, $p_month, $p_day, $p_year));
                        } else {
                          $saikoubi = "1900-01-01";
                        }
                      } else {
                        $saikoubi = null;
                      }
                      $today = date("Y\-m\-d");

//WHERE
                      $sql_event_where = " WEB.eventcode = API.eventid and WEB.available = 1 and WEB.web_available = 1 and WEB.event_id = EVTP.event_id and PURPOSE.id = EVTP.purpose_id ";
                      $sql_purpose_where = "";

//目的検索
                      if ($p_purposeid != null) {
                        //$sql_event_where .= " and purposeid = ".$mdbapp->quote($p_purposeid,'text');
                        $sql_event_where .= " and PURPOSE.purposeid = " . $mdbwww->quote($p_purposeid, 'text');
                        ;
                      }
//テーマ検索
                      if ($p_themaid != null) {
						  
						$where5 = null;
								//カテゴリを配列に
								$categories = preg_split("/,/",$p_themaid);
								if(count($categories) == 1){
									$where5 = $where5."
										(
											WEB.themeid = '".$categories[$i]."' 
										)
										";
										
									$sql_event_where .= " and WEB.themeid = '".$categories[0]."' ";
								}else{
									for ($i = 0 ; $i <count($categories); $i++) {
							
										if($where5 != null){
											$where5 = $where5." OR ";
										}
										$where5 = $where5."
										(
											WEB.themeid = '".$categories[$i]."' 
										)
										";
									}
									$sql_event_where .= " and (".$where5.")";
								}
							
						  

#echo "OK";exit;
						  
						#echo $sql_event_where;exit; 
                        #$sql_event_where .= " and WEB.themeid = " . $mdbwww->quote($p_themaid, 'text');
                      }
//キーワード検索
                      if ($p_keyword != null) {
                        $p_keyword = str_replace("　", " ", $p_keyword);     // 全角スペースを半角スペースに変換
                        $p_keyword = trim($p_keyword);     //前後の半角スペースを除去
                        $array_keyword = spliti(" ", $p_keyword);     //半角スペースにより分割
                        foreach ($array_keyword as $value) {
                          $sql_event_where .= " and WEB.web_searchkey like '%" . $mdbwww->escapePattern($value, 'text') . "%' ";
                        }
                      }
//都道府県検索
                      $sql_pref_where = "";
                      if ($p_prefid != null) {
                        $sql = " select areaid from " . MTDB . ".m_pref_area ";
                        $sql .= "where prefid = " . $mdbwww->quote($p_prefid, 'text');
                        $res = & $mdbwww->query($sql);
                        // 結果がエラーでないかどうかを常にチェック
                        if (PEAR:: isError($res)) {
                          die($res->getMessage());
                          //ログの出力を行い、エラーページへ遷移
                        }
                        $sql_pref_where = " and WEB.areaid in(";
                        $arealist = "";
                        while ($row = $res->fetchRow()) {
                          if ($arealist != "") {
                            $sql_pref_where .= ",";
                          }
                          $arealist = $row['areaid'];
                          $sql_pref_where .= $mdbwww->quote($arealist, 'text');
                        }
                        $sql_pref_where .= ") ";
                        //結果セットの開放
                        $res->free();
                      }
                      $sql_event_where .= $sql_pref_where;
//エリア検索
                      if ($p_areaid != null) {
                        $sql_event_where .= " and WEB.areaid = " . $mdbwww->quote($p_areaid, 'text');
                      }

//開催日検索
                      if ($saikoubi != null) {
                        $sql_event_where .= " and WEB.kaisaistart <= '" . $saikoubi . "'" . "\n";
                        $sql_event_where .= " and WEB.kaisaiend >= '" . $saikoubi . "'" . "\n";
                      }

#echo "OK";exit;

//イベントコード
if( isset($params['eventid']) && $params['eventid'] != ""){
	$eventid = $params["eventid"]; //入力値
    if(is_numeric ( $eventid )){
        if(strlen ($eventid) < 7){
            $eventid = "P". str_pad($eventid, 6, "0", STR_PAD_LEFT); 
        }
    }
	$sql_event_where .= " and WEB.eventcode = '" . $eventid . "'" . "\n";
}
                      if ($sql_event_where != "") {
                        $sql_event_where = " where " . $sql_event_where;
                      }

//ORDER BY
                      if ($p_sort == "new") {
                        $sql_order = " order by WEB.modified desc";
                      } elseif ($p_sort == "pop") {
                        $sql_order = " order by WEB.web_popularity_rank asc";
                      } elseif ($p_sort == "low") {
                        $sql_order = " order by adultfee asc";
                      } elseif ($p_sort == "high") {
                        $sql_order = " order by adultfee desc";
                      } else {
                        $sql_order = " order by WEB.modified desc";
                      }


                      $sql_select = " select distinct " . "\n";
                      $sql_select .= "   WEB.event_id" . "\n";
                      $sql_select .= "   ,WEB.eventcode as eventid" . "\n";
                      $sql_select .= "   ,WEB.title as eventname" . "\n";
                      $sql_select .= "   ,WEB.adult_fee as adultfee" . "\n";
                      $sql_select .= "   ,WEB.abstract as optionsetumei" . "\n";
                      $sql_select .= "   ,WEB.picture1thumbpath" . "\n";
                      $sql_select .= "   ,WEB.picture1caption" . "\n";
                      $sql_select .= "   ,WEB.web_event_contents as eventcontents" . "\n";
                      if ($p_purposeid != null) {
                        $sql_select .= "   ,WEB.purposeid" . "\n";
                        $sql_select .= "   ,WEB.purposename" . "\n";
                      }
                      $sql_select .= "   ,WEB.themeid" . "\n";
                      $sql_select .= "   ,WEB.themename as themaname" . "\n";
                      $sql_select .= "   ,WEB.areaid" . "\n";
                      $sql_select .= "   ,WEB.areaname" . "\n";
                      $sql_select .= "   ,WEB.kaisaistart" . "\n";
                      $sql_select .= "   ,WEB.kaisaiend" . "\n";
                      $sql_select .= "   ,WEB.web_popularity_rank" . "\n";
                      $sql_select .= "   ,WEB.modified" . "\n";
					  
					  $sql_select .= "   ,API.adultfee as api_adultfee" . "\n";
					  
					  $sql_select .= ",API.syugouplace as api_syugouplace";
						$sql_select .= ",API.mapurl as api_mapurl";
						#$sql_select .= ",API.starttime";
						#$sql_select .= ",API.endtime";
						
						#$sql_select .= ",API.optionsetumei";
						$sql_select .= ",API.picture1thumbpath as api_picture1thumbpath";
						$sql_select .= ",API.picture2thumbpath as api_picture2thumbpath";
						$sql_select .= ",API.picture3thumbpath as api_picture3thumbpath";
						$sql_select .= ",API.eventcontents as api_eventcontents";
						#$sql_select .= ",API.kokogaosusume";
						$sql_select .= ",API.schedule as api_schedule";
						$sql_select .= ",API.minsaikounum as api_minsaikounum";
						$sql_select .= ",API.uketukekanoubi as api_uketukekanoubi";
						$sql_select .= ",API.cancelkiteikbn as api_cancelkiteikbn";
					
					  
                      $sql_select .= " from " . PORSDB . ".d_web_search_japanese_events as WEB, " . PORSDB . ".d_api_search_events as API, " . APPDB . ".events_purposes as EVTP, " . APPDB . ".purposes as PURPOSE" . "\n";
                      $sql_select .= $sql_event_where . "\n";
					  
               
                      #$sql_select .= $sql_order . $sql_limit . "\n";
					  $sql_select .= $sql_order . "\n";



//件数指定取得
                      $sql = $sql_select;
#echo $sql;exit;

                      $res = & $mdbwww->query($sql);
// 結果がエラーでないかどうかを常にチェック
                      if (PEAR:: isError($res)) {
                        die($res->getMessage());
                        //ログの出力を行い、エラーページへ遷移
                      }

#echo $sql;exit;

//描画開始
$data_json = array();
$list = array();
$cnt = 0;
                      while ($row = $res->fetchRow()) {
//イベントID
                        
						$eventid = $row['eventid'];
//イベント名
                        if ($row['eventname'] == null) {
                          $eventneme = "&nbsp;";
                        } else {
                          $eventneme = htmlspecialchars($row['eventname']);
                        }
//詳細リンク
                        if ($p_year != null && $p_month != null) {
                          $url = "/book/calendar.php?eventid=".$eventid."&y=".$k_year."&m=".$k_month;
                        } else {
                          $url = "/book/calendar.php?eventid=".$eventid;
                        }
//サムネイル画像
                        if ($row['picture1thumbpath'] == null) {
                          $picture1thumbpath = null;
						  $img = "";
                        } else {
                          //20100707.sijam.noguchi.change
                          //$picture1thumbpath = "<img src=\"".$row['picture1thumbpath']."\" alt=\"".$row['picture1caption']."\" />";
                          $picture1thumbpath = "<img src=\"" . MAP_URL . $row['picture1thumbpath'] . "\" alt=\"" . $row['picture1caption'] . "\" />";
						  $img = MAP_URL . $row['picture1thumbpath'];
                        }
						
						if ($row['picture1thumbpath'] == null) {
							$picture1caption = "";
						}else{
							$picture1caption = $row['picture1caption'];
						}
//開催期間
                        if (blnCheckDate($row['kaisaistart'])) {
                         
                            $kaisaistart = strftime("%Y年%m月%d日", strtotime($row['kaisaistart']));
                         
                        } else {
                          $kaisaistart = null;
                        }
                        if (blnCheckDate($row['kaisaiend'])) {
                         
                            $kaisaiend = strftime("%Y年%m月%d日", strtotime($row['kaisaiend']));
                       
                        } else {
                          $kaisaiend = null;
                        }
                        if ($kaisaistart != null && $kaisaistart != null) {
                          $saikouday = htmlspecialchars($kaisaistart . " 〜 " . $kaisaiend);
                        } else {
                          $saikouday = htmlspecialchars($kaisaistart . $kaisaiend);
                        }
                        if ($saikouday == null) {
                          $saikouday = "&nbsp;";
                        }
//エリア
                        if ($row['areaname'] == null) {
                          $area = "&nbsp;";
                        } else {
                          $area = htmlspecialchars($row['areaname']);
                        }
//料金
                        if ($row['adultfee'] == null) {
                          $adultfee = null;
                        } else {
                          $minfee =  "";
                          $minfeejp = "～";
                          $adultfee = $minfee . "&nbsp;" . htmlspecialchars(number_format($row['adultfee']) . "円" . $minfeejp);
                        }
//ガイドなど
                        if ($row['optionsetumei'] == null) {
                          $optionsetumei = "&nbsp;";
                        } else {
                          $optionsetumei = htmlspecialchars("（" . $row['optionsetumei'] . "）");
                        }
//イベント内容
                        if ($row['eventcontents'] == null) {
                          $eventcontents = "&nbsp;";
                        } else {
                          $eventcontents = mb_substr(strip_tags($row['eventcontents']), 0, 250, "UTF-8");
                        }
//テーマ
                        if ($row['themaname'] == null) {
                          $themaname = "&nbsp;";
                        } else {
                          $themaname = htmlspecialchars($row['themaname']);
                        }

/*
$data_json[$cnt]['eventid'] = $eventid;
$data_json[$cnt]['themaname'] = $themaname;

$data_json[$cnt]['eventneme'] = $eventneme;
$data_json[$cnt]['url'] = $url;
$data_json[$cnt]['picture1thumbpath'] = $picture1thumbpath;
$data_json[$cnt]['eventcontents'] = $eventcontents;
$data_json[$cnt]['saikouday'] = $saikouday;
$data_json[$cnt]['area'] = $area;
$data_json[$cnt]['adultfee'] = $adultfee;
$data_json[$cnt]['optionsetumei'] = $optionsetumei;
$data_json[$cnt]['themaname'] = $themaname;

$data_json[$cnt]['api_adultfee'] = $row['api_adultfee'];
$data_json[$cnt]['api_syugouplace'] = $row['api_syugouplace'];
$data_json[$cnt]['api_mapurl'] = $row['api_mapurl'];
$data_json[$cnt]['api_picture1thumbpath'] = $row['api_picture1thumbpath'];
$data_json[$cnt]['api_picture2thumbpath'] = $row['api_picture2thumbpath'];
$data_json[$cnt]['api_picture3thumbpath'] = $row['api_picture3thumbpath'];
$data_json[$cnt]['api_eventcontents'] = $row['api_eventcontents'];
$data_json[$cnt]['api_schedule'] = $row['api_schedule'];

$data_json[$cnt]['api_minsaikounum'] = $row['api_minsaikounum'];
$data_json[$cnt]['api_uketukekanoubi'] = $row['api_uketukekanoubi'];
$data_json[$cnt]['api_cancelkiteikbn'] = $row['api_cancelkiteikbn'];

$data_json[$cnt]['api_schedule'] = $row['api_schedule'];
*/

#欲しいものリスト
$list[$eventid]['title'] = $eventneme;
$summary = $eventcontents;
$summaryArr = explode("&nbsp;",$summary);
$list[$eventid]['summary_caption'] = $summaryArr[0];
$list[$eventid]['summary'] = $summaryArr[1];
$list[$eventid]['link'] = $url;
$list[$eventid]['alt'] = $picture1caption;
$list[$eventid]['img'] = $img;
$list[$eventid]['img2'] = $row['api_picture2thumbpath'];
$list[$eventid]['img3'] = $row['api_picture3thumbpath'];
$list[$eventid]['term'] = $saikouday;
$list[$eventid]['area'] = $area;
$list[$eventid]['price'] = "<em>".$adultfee."</em>".$optionsetumei;
$list[$eventid]['api_syugouplace'] = $row['api_syugouplace'];


						$cnt++;
                      }
//描画終了
//結果セットの開放
                      $res->free();

return $list;

	
}
function blnPrmCheckDate($year, $month, $day, $i) {
  if (checkdate($month, $day, $year)) {
	$day = strtotime(date("Ymd", mktime(0, 0, 0, $month, $day, $year)));
	$minday = strtotime(date("Ymd"));
	$maxday = strtotime(date("Ymd", mktime(0, 0, 0, date("m") + ($i + 1), 0, date("Y"))));
	if ($day < $minday) {
	  return false;
	} elseif ($maxday < $day) {
	  return false;
	}
  } else {
	return false;
  }
  return true;
}

function blnCheckDate($ymd_date) {

  $date_pattern = "/(\d{4})-(\d{2}|\d)-(\d{2}|\d)/";

  if (preg_match($date_pattern, $ymd_date, $match)) {
	if (checkdate($match[2], $match[3], $match[1]))  // month, day, year
	  return TRUE;
  }
  return FALSE;
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

<?php /*
<link href="/css/base_renew.css" rel="stylesheet" type="text/css">
<link href="/css/topstyle_renew.css" rel="stylesheet" type="text/css">
<link href="/css/richsearch.css" rel="stylesheet" type="text/css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery.getUrlParam.js"></script>
<script type="text/javascript" src="/js/jquery.cookie.js"></script>
*/ ?>
<style>
#special1 a.btn_detail , #special2 a.btn_detail{
	display:block;
	border-radius: 6px;
	-webkit-border-radius: 6px;
	-moz-border-radius: 6px;	
	 font-size:20px;
	 font-weight:bold;
	 color:#FFF;
	 
	 text-align:center;
	 width:100%;
	 margin:0 auto;
	 
	 text-decoration:none;
	 float:right;
	 margin-bottom:20px;
	 /*
	 background:#ED902C;
	 box-shadow:0 4px 0 #E56D00;
	 */
	 background:#e67e22;
	 box-shadow:0 4px 0 #dd5900;
	 
	 padding-top:6px;
	 padding-bottom:2px;
}
#special2 a.btn_detail{
	margin-bottom:0;
}
#special1 a.btn_detail img , #special2 a.btn_detail img{
	margin-right:7px;	
	width:28px;
	position:relative;
	top:-4px;
}
#special1 a:hover.btn_detail , #special2 a:hover.btn_detail{
	background:#f8d8ba;
	color:#0166FF;
}


#special1 h2 , #special2 h2{border-left:10px solid #<?php echo $h2color;?>;}

/* ここから新規 */
#special1,#special2{
	font-size:14px;line-height:1.3em;
}

#special1 > ul{
	width:920px;
	position:relative;
	margin:20px auto 100px auto;
}
#special1 > ul > li{
	padding:20px;
	box-sizing:border-box;
	margin-bottom:30px;
	
	background:#FFF;
	box-shadow:0 0 18px #C0C0C0;
	position:relative;
	overflow:hidden;
	border:1px solid #FFF;
}
#special1 > ul > li:hover{
	box-shadow:0 0 18px #dd5900;
	border:1px solid #dd5900;
}
#special1 > ul > li > ul{
	width:100%;
}
#special1 > ul > li > ul > li{
	float:left;	
	line-height:1.5;
}
#special1 > ul > li > ul > li:nth-child(5n+1){
	width:50%;
	margin-bottom:25px;
}
#special1 > ul > li > ul > li:nth-child(5n+2){
	width:50%;
	text-align:right;
	margin-bottom:25px;
}
#special1 > ul > li > ul > li:nth-child(5n+2) a{
	background:#FFFD7C;
	border-radius: 6px;
	-webkit-border-radius: 6px;
	-moz-border-radius: 6px;
	box-shadow:0 3px 0 #BFBFBF;
	padding:5px 30px;
	text-decoration:none;
	color:#000 !important;
}
#special1 > ul > li > ul > li:nth-child(5n+3){
	width:100%;
	margin-bottom:15px;
}
#special1 > ul > li > ul > li:nth-child(5n+4){
	width:47%;
	text-align:center;
	margin-right:2%;
}
#special1 > ul > li > ul > li:nth-child(5n+4) img{
	margin-bottom:10px;
}
#special1 > ul > li > ul > li:nth-child(5n){
	width:51%;
	font-size:16px;
}

#special2 > ul{
	width:920px;
	margin:20px auto;
	position:relative;
	overflow:hidden;
	margin:20px auto;
}
#special2 > ul:after{
	margin-bottom:100px;
}
#special2 > ul > li{
	padding:20px;
	box-sizing:border-box;
	margin-bottom:30px;
	
	background:#FFF;
	box-shadow:0 0 18px #C0C0C0;
	position:relative;
	overflow:hidden;
	
	float:left;
	width:47%;
	margin:1.5%;
	border:1px solid #FFF;
}
#special2 > ul > li:hover{
	box-shadow:0 0 18px #dd5900;
	border:1px solid #dd5900;
}
#special2 > ul > li > ul{
	width:100%;
}
#special2 > ul > li > ul > li{
	float:left;	
	line-height:1.5;
}
#special2 > ul > li > ul > li:nth-child(5n+1){
	width:50%;
	margin-bottom:25px;
}
#special2 > ul > li > ul > li:nth-child(5n+2){
	width:50%;
	text-align:right;
	margin-bottom:25px;
}
#special2 > ul > li > ul > li:nth-child(5n+2) a{
	background:#FFFD7C;
	border-radius: 6px;
	-webkit-border-radius: 6px;
	-moz-border-radius: 6px;
	box-shadow:0 3px 0 #BFBFBF;
	padding:5px 30px;
	text-decoration:none;
	color:#000 !important;
}
#special2 > ul > li > ul > li:nth-child(5n+3){
	width:100%;
	margin-bottom:15px;
	line-height:1.3;
	height:60px;
}
#special2 > ul > li > ul > li:nth-child(5n+4){
	width:100%;
	text-align:center;
	margin-right:2%;
	height:360px;
}
#special2 > ul > li > ul > li:nth-child(5n+4) img{
	margin-bottom:10px;
}
#special2 > ul > li > ul > li:nth-child(5n){
	width:100%;
	font-size:14px;
/*	height:320px;*/
}
#special2 > ul > li > ul > li:nth-child(5n) p{
	height:120px;
}
.special_title{
	font-family:Meiryo,"メイリオ",  "ヒラギノ角ゴ Pro W3", "Hiragino Kaku Gothic Pro", Osaka, "ＭＳ Ｐゴシック", "MS PGothic", sans-serif;
	font-size:16px;
	margin-bottom:20px;
	background:#F5F2E9;
	padding:7px;
	box-sizing:border-box;
	line-height:1.5;
	width:100%;
}

#special1 h2 , #special2 h2{font-size:18px; font-weight:bold;border-bottom:1px solid #DDD;padding:12px 18px;margin-bottom:20px;}
#special1 h3 a , #special2 h3 a{
	font-weight:bold;
	color:#0166FF;

}
#special1 h3 a{
	font-size:18px;
	line-height:1.5;
}
#special2 h3 a{
	font-size:16px;
}
.sp_lead{
	font-size:14px;
	padding:0 15px;
	line-height:1.5;
	margin-bottom:20px;
}
.sp_result{
	font-size:16px;
}
.sp_result em{
	font-style:normal;
	font-size:18px;
}
#special1 ol , #special2 ol{
	background:#FFFEDD;
	width:100%;
	position:relative;
	overflow:hidden;
	padding:10px;
	box-sizing:border-box;
	margin-top:15px;
	margin-bottom:15px;
}
#special1 ol li{
	float:left;
	font-size:14px;
}
#special2 ol li{
	float:left;
	font-size:12px;
	line-height:1.2;
	height:28px;
	box-sizing:border-box;
	
	
}
#special1 ol li:nth-child(2n+1){
	width:25%;
	margin-left:2%;
	margin-bottom:2px;
	margin-top:5px;
	
}
#special2 ol li:nth-child(2n+1){
	width:25%;
	margin-left:2%;
	margin-bottom:2px;
	margin-top:2px;
}
#special1 ol li:nth-child(2n+1) img , #special2 ol li:nth-child(2n+1) img{
	margin-right:7px;
}
#special1 ol li:nth-child(2n){
	width:65%;
	margin-top:5px;
	margin-bottom:10px;
	
}
#special2 ol li:nth-child(2n){
	width:65%;
	margin-top:5px;
	margin-bottom:2px;
}
#special1 ol > li em , #special2 ol > li em{
	color:#FF4499;
	font-style:normal;
}
</style>
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
if(isset($canonicalpc) && $canonicalpc != ""):
	echo $canonicalpc."\n";
endif;
if(isset($alternate) && $alternate != ""):
	echo $alternate."\n";
endif;
?>
      <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
<body>
    <?php include_once("/_data/tags/body_tag.php"); ?>
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

<div class="marginB20"><img src="/images/special/<?php echo $image;?>" width="100%" /></div>



<div class="special_title"><?php echo $caption;?></div>

<h2><?php echo $h2;?></h2>

<div class="sp_lead">
<?php echo $h2lead;?>
</div>


<div class="sp_result">
全<em><?php echo count($list);?></em>件
</div>

          <div class="search-body">
<?php foreach($list as $k => $v):

$size = getimagesize($v['img']);
$width = $size[0];
$height = $size[1];
?>
            <div class="sr-item">
              <h3 class="headline03"><span><?php
    if(mb_strlen($v['title'],'UTF-8') > $offset_title_pc):
    	echo mb_substr($v['title'],0,$offset_title_pc,'UTF-8')."...";
    else:
    	echo $v['title'];
    endif;
	?></span></h3>
              <div class="tours">
                <ul class="photo-list">
                  <li><a class="trans" href="<?php echo $v['link'];?>"><img src="<?php echo $v['img'];?>" alt="photo"></a></li>
                  <li><a class="trans" href="<?php echo $v['link'];?>"><img src="<?php echo $v['img2'];?>" alt="photo"></a></li>
                  <li><a class="trans" href="<?php echo $v['link'];?>"><img src="<?php echo $v['img3'];?>" alt="photo"></a></li>
                </ul>
                <p class="desc"><strong>
	<?php 
	if(mb_strlen($v['summary_caption'],'UTF-8') > $offset_caption_pc):
    	echo mb_substr($v['summary_caption'],0,$offset_caption_pc,'UTF-8')."...";
    else:
    	echo $v['summary_caption'];
    endif;
	?></strong><br />

	<?php 
    if(mb_strlen($v['summary'],'UTF-8') > $offset_summary_pc):
    	echo mb_substr($v['summary'],0,$offset_summary_pc,'UTF-8')."...";
    else:
    	echo $v['summary'];
    endif;
    ?></p>
                <div class="panel">
                  <div class="panel-heading">
                    <div class="left">
                      <span class="orange">WEB予約限定プラン</span>
                      <span class="green">クレジットカード決済</span>
                    </div>
                    <div class="right">
<?php /*                      <a class="like" href="#"><i class="fa fa-heart"></i>行きたい！</a><span class="amount">2114</span>
                      <a class="favorite01" href="#"><img src="../img/common/icon_favorite.png" alt="favorite">お気に入りリストに追加</a>*/ ?>
                    </div>
                  </div>
                  <div class="panel-body">
                    <table>
                      <tr>
                        <th>商品番号</th>
                        <td class="col01"><?php echo $k; ?></td>
                        <th>口コミ</th>
                        <td class="rate01">
                        <!--
                        <span><img src="../img/common/icon_star.png" alt="★"></span><span><img src="../img/common/icon_star.png" alt="★"></span><span><img src="../img/common/icon_star.png" alt="★"></span><span><img src="../img/common/icon_star.png" alt="★"></span><span><img src="../img/common/icon_star.png" alt="★"></span><small>（<a href="#">21件</a>）</small>
                        -->
                        </td>
                      </tr>
                      <tr>
                        <th>開催期間</th>
                        <td class="col01"><?php echo $v['term'];?></td>
                        <th>エリア</th>
                        <td><?php echo $v['area'];?></td>
                      </tr>
                      <tr>
                        <th>料金</th>
                        <td colspan="3"> <?php echo $v['price'];?></td>
                      </tr>
                      <tr>
                        <th>集合場所</th>
                        <td colspan="3"><?php echo $v['api_syugouplace']; ?></td>
                      </tr>
                    </table>
                  </div>
                  <p class="btn-detail"><a href="<?php echo $v['link'];?>">詳細を見る</a></p>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
<!--
<ul>
<?php foreach($list as $k => $v): 

$size = getimagesize($v['img']);
$width = $size[0];
$height = $size[1];
?>
<li>

	<ul>
    <li>商品番号：<?php echo $k;?></li>
    <li><a href="/book/sendmobile.php?sp=<?php echo str_replace("?testview=1","",str_replace("/","",$_SERVER["REQUEST_URI"]));?>&eventid=<?php echo $k;?>">スマホに情報を送る</a></li>
    <li><h3><a href="<?php echo $v['link'].$prm_asid;?>">
	<?php
    if(mb_strlen($v['title'],'UTF-8') > $offset_title_pc):
    	echo mb_substr($v['title'],0,$offset_title_pc,'UTF-8')."...";
    else:
    	echo $v['title'];
    endif;
	?></a></h3></li>
    <li>
    <div><a href="<?php echo $v['link'];?><?php echo $prm_asid; ?>">
    <?php if($width < $height): ?>
    	<img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" height="309">
    <?php else: ?>    
        <?php if($height > 0.75 * $width): ?>
        <img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" height="309">
        <?php else: ?>
        <img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" width="100%">
        <?php endif; ?>
    <?php endif; ?>
    </a></div>

	<?php echo $v['alt'];?></li>
    <li><p>
	<strong>
	<?php 
	if(mb_strlen($v['summary_caption'],'UTF-8') > $offset_caption_pc):
    	echo mb_substr($v['summary_caption'],0,$offset_caption_pc,'UTF-8')."...";
    else:
    	echo $v['summary_caption'];
    endif;
	?></strong><br />

	<?php 
    if(mb_strlen($v['summary'],'UTF-8') > $offset_summary_pc):
    	echo mb_substr($v['summary'],0,$offset_summary_pc,'UTF-8')."...";
    else:
    	echo $v['summary'];
    endif;
    ?>
	
    </p>


	<ol>
    <li><img src="/images/richsearch/icon_calendar.png" alt="" width="15" />開催期間</li>
    <li><?php echo $v['term'];?></li>
    <li><img src="/images/richsearch/icon_marker.png" alt="" width="15" />エリア</li>
    <li><?php echo $v['area'];?></li>
    <li><img src="/images/richsearch/icon_star.png" alt="" width="15" />料金</li>
    <li><?php echo $v['price'];?></li>
    </ol>

	<a href="<?php echo $v['link'];?><?php echo $prm_asid; ?>" class="btn_detail"><img src="/images/richsearch/icon_mouse.png" /><?php echo $btn_name;?></a></li>
    </ul>


</li>
<?php endforeach; ?>

</ul>
-->

</div><!-- search body -->

<div class="clearfix"></div>

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
if(isset($canonicalsp) && $canonicalsp != ""):
	echo $canonicalsp."\n";
endif;
?>
      <?php include_once("/_data/tags/head_tag.php"); ?>
</head>
  <body>
    <?php include_once("/_data/tags/body_tag.php"); ?>
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
          	<h2 class="sec-ttl"><?php echo $special_title; ?></h2>
            <p class="desc"><?php echo $caption;?></p>
          </div>
        </div>
        <div class="geihinkan02">
          <div class="wrapper">
            <h3 class="ttl"><?php echo $h2;?></h3>
            <p class="txt"><?php echo $h2lead;?></p>
          </div>
        </div>
        
          <div class="search-results">
          <div class="search-heading">
            
            <div class="group">
              
              <ul class="clearfix">
                <li class="count">全 <em><?php echo count($list);?></em> 件</li>
                <!--<li><span>15件</span></li>-->
              </ul>
            </div>
          </div><!-- search-heading -->
          <div class="search-body">





<?php foreach($list as $k => $v): 

$size = getimagesize($v['img']);
$width = $size[0];
$height = $size[1];
#echo $v['link'];
?>

            <div class="sr-item">
              <h3 class="headline03"><?php echo $v['title']; ?></h3>
              <div class="tours">
                <div class="panel">
                  <div class="panel-heading">
                    <span class="green">クレジットカード決済</span>
                    <span class="blue w01">振込票決済</span>
                  </div>
                  <div class="panel-body">
                    <div class="tourist">
                      <a href="<?php echo $v['link'];?><?php echo $prm_asid; ?>">
                        <p class="image">
                            <?php 
							if($size):
							
							if($column_sp == 1): ?>
                                <img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" height="200">   
                            <?php else: ?>
                                <img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" width="100%">
                            <?php endif; 
							
							endif;
							?>
                        </p>
                        <div class="ttl">
                          <p class="desc">
						  <?php 
						  	echo $v['summary_caption'];  
							
                          $offset = 68;
						  $tmp = $v['summary'];
						  if(mb_strlen( $tmp ,'UTF-8') > $offset)
								$tmp = mb_substr( $tmp , 0 , $offset , 'UTF-8')."...";
						 
						   echo $tmp;
						  	
						  ?></p>
                          <?php /*
                          <p class="rate01"><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><span><img src="/sp/common2/img/common/icon_star.png" alt="★"></span><small>（XX件）</small></p>
						  */ ?>
                        </div>
                      </a>
                    </div>
                    <table>
                      <tr>
                        <th>期間</th>
                        <td><?php echo $v['term'];?></td>
                      </tr>
                      <tr>
                        <th>エリア</th>
                        <td><?php echo $v['area'];?></td>
                      </tr>
                      <tr>
                        <th>料金</th>
                        <td><?php echo $v['price']; ?></td>
                      </tr>
                    </table>
                  </div>
                  <div class="vote-bl">
                    <?php /* <a class="like" href="#"><i class="fa fa-heart"></i>行きたい！</a><span class="amount">XXXX</span> */ ?>
                    <a class="favorite01" href="#"><img src="/sp/common2/img/common/favorite.png" alt="favorite">お気に入りリストに追加</a>
                  </div>
                  <p class="text-center"><a class="btn-detail" href="<?php echo $v['link'];?><?php echo $prm_asid; ?>">詳細を見る</a></p>
                </div>
              </div>
            </div>
            
<?php /*
<li>

	<ul>
    <li><h3><a href="<?php echo $v['link'];?><?php echo $prm_asid; ?>"><?php echo $v['title']; ?></a></h3></li>
    <li>
    <div><a href="<?php echo $v['link'];?><?php echo $prm_asid; ?>">
    
    <?php if($column_sp == 1): ?>
		<img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" height="200">   
    <?php else: ?>
    	<img src="<?php echo $v['img'];?>" alt="<?php echo $v['alt'];?>" width="100%">
    <?php endif; ?>
    
    </a></div>

	</li>
    <li>
    
    <?php if($column_sp == 1): ?>
    <p>

	<span><strong><?php echo $v['summary_caption'];?></strong></span>

	<?php 
    if(mb_strlen($v['summary'],'UTF-8') > 65):
    	echo mb_substr($v['summary'],0,65,'UTF-8')."...";
    else:
    	echo $v['summary'];
    endif;
    ?>
	
    </p>

	<?php endif; ?>

	<table>
    <tr>
    <td>開催期間</td>
    <td><?php echo $v['term'];?></td>
    </tr>
    <tr>
    <td>エリア</td>
    <td><?php echo $v['area'];?></td>
    </tr>
    <tr>
    <td>料金</td>
    <td><?php echo $v['price']; ?></td>
    </tr>
    </table>
    
    </li>
	<li>
    
    <?php if($column_sp == 2): ?>
    <p>

	<span><?php echo $v['summary_caption'];?></span>

	<?php 
    if(mb_strlen($v['summary'],'UTF-8') > 65):
    	echo mb_substr($v['summary'],0,65,'UTF-8')."...";
    else:
    	echo $v['summary'];
    endif;
    ?>
	
    </p>

	<?php endif; ?>
    
	<a href="<?php echo $v['link'];?><?php echo $prm_asid; ?>" class="btn_detail"><img src="/images/richsearch/icon_tap.png" />詳細を見る</a>
    </li>
    </ul>


</li>
*/ ?>
<?php endforeach; ?>

            </div><!-- search-body -->
            </div><!-- search-results -->

<div class="mb20"></div>




      <?php require_once("2018/footer.html"); ?>
<?php endif; ?>