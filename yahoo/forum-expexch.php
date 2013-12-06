<?php
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/simple_html_dom.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC_Forum.class.php");

	$page_first = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 1;
	$page_limit = (!empty($argv[2]) && intval($argv[2]) > 0) ? intval($argv[2]) : 3;
	$rows = array();
	$urls = get_url_list("http://verywed.com/forum/expexch/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit);

	foreach($urls as $i=>$url) {

		echo ($i + 1) . "- " . $url . "\n";

		if(preg_match("/(2757828|2769836)+/", $url)) {
			echo "- This URL is block\n";
			continue;
		}

		if(!preg_match("/([0-9]+)-1.html$/i", $url, $matchs) || empty($matchs[1])) {
			echo "- URL format is fail\n";
			continue;
		}

		$id = $matchs[1];

		$html = file_get_html($url);
		$YUF = new Yahoo_UGC_Forum($id);
		$YUF->set_url($url);
		$YUF->set_category(array("婚禮", "結婚", "禮俗"));

		if(!$YUF->set_thread_url($url)) { echo "-- thread_url is fail\n"; continue; }
		if(!$YUF->set_title($html)) { echo "-- title is fail\n"; continue; }
		if(!$YUF->set_body($html)) { echo "-- body is fail\n"; continue; }
		if(!$YUF->set_created_time($html)) { echo "-- created_time is fail\n"; continue; }

		$YUF->set_creator($html);
		$YUF->set_no_of_replies_views($html);
		$YUF->set_tag($html);
		$YUF->set_author_thumb($html);
		$YUF->set_image($html);
		$rows[$i] = $YUF->xml_mapping();

	}

  echo "Result: " . Yahoo_UGC_Forum::buildXML("tw_verywed_" . date("Ymd_His") . "_1", $rows) . "\n";
?>
