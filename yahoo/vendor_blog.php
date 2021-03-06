<?php
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/simple_html_dom.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC_VenBlog.class.php");

	$page_first = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 1;
	$page_limit = (!empty($argv[2]) && intval($argv[2]) > 0) ? intval($argv[2]) : 3;
	$rows = array();
	$urls = get_url_list("http://verywed.com/classified/blog.php?p={page}", "div#list_box_text a.link-71", $page_first, $page_limit);

	foreach($urls as $i=>$url) {

		echo ($i + 1) . "- " . $url . "\n";

		$html = file_get_html($url);
		$YUF = new Yahoo_UGC_VenBlog();
		$YUF->set_url($url);
		$YUF->set_category(array("婚禮", "商品優惠", "婚紗公司", "新娘秘書", "婚禮紀錄"));

		if(!$YUF->set_creator($html)) { echo "-- creator is fail\n"; continue; }
		if(!$YUF->set_blog_url($html)) { echo "-- blog_url is fail\n"; continue; }
		if(!$YUF->set_title($html)) { echo "-- title is fail\n"; continue; }
		if(!$YUF->set_body($html)) { echo "-- body is fail\n"; continue; }
		if(!$YUF->set_blog_title()) { echo "-- blog_title is fail\n"; continue; }
		if(!$YUF->set_created_time($html)) { echo "-- created_time is fail\n"; continue; }
		$YUF->set_author_thumb($html);
		$YUF->set_image($html);
		$rows[$i] = $YUF->xml_mapping();

	}
	echo "Result: " . Yahoo_UGC_VenBlog::buildXML("tw_verywed_" . date("Ymd_His") . "_4", $rows) . "\n";
?>
