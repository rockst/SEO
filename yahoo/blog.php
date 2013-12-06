<?php
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/simple_html_dom.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC_Blog.class.php");

	$page_first = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 1;
	$page_limit = (!empty($argv[2]) && intval($argv[2]) > 0) ? intval($argv[2]) : 3;
	$data_ary = array(
		array("http://verywed.com/vwblog/channel/wedding?p={page}", "div#articles div.excerpt h5 a", array("婚禮", "結婚", "禮俗", "婚紗", "新娘秘書", "婚禮紀錄")),
		array("http://verywed.com/vwblog/channel/baby?p={page}", "div#articles div.excerpt h5 a", array("寶寶", "教養", "健康")),
		array("http://verywed.com/vwblog/channel/food?p={page}", "div#articles div.excerpt h5 a", array("食譜", "美食", "餐廳")),
		array("http://verywed.com/vwblog/channel/travel?p={page}", "div#articles div.excerpt h5 a", array("旅遊", "蜜月", "自助婚紗")),
		array("http://verywed.com/vwblog/channel/wedlife?p={page}", "div#articles div.excerpt h5 a", array("婚後", "婆媳", "心情"))
	);
	foreach($data_ary as $data) {
		echo $data[0] . ":\n";
		$rows = array();
		$urls = get_url_list($data[0], $data[1], $page_first, $page_limit);
		foreach($urls as $i=>$url) {
			echo ($i + 1) . "- " . $url . "\n";
			if(!preg_match("/^http:\/\/verywed.com\/vwblog\/(.*)\/article\/[0-9]+/i", $url, $matchs) || empty($matchs[1])) {
				echo "- URL format is fail\n";
				continue;
			}
			$html = file_get_html($url);
			$YUF = new Yahoo_UGC_Blog();
			$YUF->set_url($url);
			$YUF->set_category($data[2]);

			if(!$YUF->set_blog_url()) { echo "-- blog_url is fail\n"; continue; }
			if(!$YUF->set_title($html)) { echo "-- title is fail\n"; continue; }
			if(!$YUF->set_body($html)) { echo "-- body is fail\n"; continue; }
			if(!$YUF->set_blog_title($html)) { echo "-- blog_title is fail\n"; continue; }
			if(!$YUF->set_created_time($html)) { echo "-- created_time is fail\n"; continue; }

			$YUF->set_creator($html);
			$YUF->set_tag($html);
			$YUF->set_author_thumb($html);
			$YUF->set_image($html);
			$rows[$i] = $YUF->xml_mapping();
		}
		echo "Result: " . Yahoo_UGC_Blog::buildXML("tw_verywed_" . date("Ymd_His") . "_3", $rows) . "\n";
		sleep(1);
	}
?>
