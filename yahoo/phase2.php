<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/simple_html_dom.php");

	while(1) {
		$MongoCursor = $MongoColl->find(array("stage" => 1, "msg" => ""))->limit(1000);
		$i = 0;
		foreach($MongoCursor as $document) {
			$url = $document["key"];
			$MongoIDObj = $document["_id"];
			$html = file_get_html($url);
			echo ($i + 1) . "- " . $url . "\n";
			if(preg_match("/(essence|forum)+/i", $document["type"])) { // forum
  			include_once(dirname(__FILE__) . "/Yahoo_UGC_Forum.class.php");
				if(!preg_match("/([0-9]+)-1.html$/i", $url, $matchs) || empty($matchs[1])) { phase2_error("URL Format is fail"); continue; }
				$YUF = new Yahoo_UGC_Forum($matchs[1]);
				$YUF->set_url($url);
				if(preg_match("/expexch/i", $url)) {
					if(preg_match("/(2757828|2769836)+/", $url)) { phase2_error("This URL is block"); continue; }
					$YUF->set_category(array("婚禮", "結婚", "禮俗"));
				} else if(preg_match("/wedlife/i", $url)) {
					$YUF->set_category(array("婚後", "懷孕", "寶寶", "婆媳", "教養"));
				} else if(preg_match("/trade/i", $url)) {
					$YUF->set_category(array("團購", "交易", "買賣", "合購"));
				} else if(preg_match("/travelcomp/i", $url)) {
					$YUF->set_category(array("婚禮", "旅遊", "蜜月", "自助旅行"));
				} else if(preg_match("/mtalks/i", $url)) {
					$YUF->set_category(array("婚禮", "優惠", "婚紗", "新秘", "婚紀"));
				} else if(preg_match("/hongkong/i", $url)) {
					$YUF->set_category(array("婚禮", "香港", "自助婚紗"));
				}
				if($document["type"] == "essence") {
					$YUF->set_quality_type("collections");
				}
				if(!$YUF->set_thread_url($url)) 		{ phase2_error("thread_url is fail"); continue; }
				if(!$YUF->set_title($html)) 				{ phase2_error("title is fail"); continue; }
				if(!$YUF->set_body($html)) 					{ phase2_error("body is fail"); continue; }
				if(!$YUF->set_created_time($html)) 	{ phase2_error("created_time is fail"); continue; }
				$YUF->set_creator($html);
				$YUF->set_no_of_replies_views($html);
				$YUF->set_tag($html);
				$YUF->set_author_thumb($html);
				$YUF->set_image($html);
			} else if(preg_match("/^blog$/i", $document["type"])) { // blog
				include_once(dirname(__FILE__) . "/Yahoo_UGC_Blog.class.php");
				if(!preg_match("/^http:\/\/verywed.com\/vwblog\/(.*)\/article\/[0-9]+/i", $url, $matchs) || empty($matchs[1])) { phase2_error("URL format is fail"); continue; }
				$YUF = new Yahoo_UGC_Blog();
				$YUF->set_url($url);
				$YUF->set_category(array("婚禮", "結婚", "禮俗"));
				if(!$YUF->set_blog_url()) 					{ phase2_error("blog_url is fail"); continue; }
				if(!$YUF->set_title($html)) 				{ phase2_error("title is fail"); continue; }
				if(!$YUF->set_body($html)) 					{ phase2_error("body is fail"); continue; }
				if(!$YUF->set_blog_title($html)) 		{ phase2_error("blog_title is fail"); continue; }
				if(!$YUF->set_created_time($html)) 	{ phase2_error("created_time is fail"); continue; }
				$YUF->set_creator($html);
				$YUF->set_tag($html);
				$YUF->set_author_thumb($html);
				$YUF->set_image($html);
			} else if(preg_match("/^ven_blog$/i", $document["type"])) { // ven_blog
				include_once(dirname(__FILE__) . "/Yahoo_UGC_VenBlog.class.php");
				$YUF = new Yahoo_UGC_VenBlog();
				$YUF->set_url($url);
				$YUF->set_category(array("婚禮", "優惠", "婚紗", "新秘", "婚紀"));
				if(!$YUF->set_creator($html)) 			{ phase2_error("creator is fail"); continue; }
				if(!$YUF->set_blog_url($html)) 			{ phase2_error("blog_url is fail"); continue; }
				if(!$YUF->set_title($html)) 				{ phase2_error("title is fail"); continue; }
				if(!$YUF->set_body($html)) 					{ phase2_error("body is fail"); continue; }
				if(!$YUF->set_blog_title()) 				{ phase2_error("blog_title is fail"); continue; }
				if(!$YUF->set_created_time($html)) 	{ phase2_error("created_time is fail"); continue; }
				$YUF->set_author_thumb($html);
				$YUF->set_image($html);
			}
			$status = update_mongodb($MongoColl, $MongoIDObj, array("stage" => 2, "data" => $YUF->xml_mapping()));
			echo "-- Update Status: " . $status . "\n";
			$i++;
		}
		echo "Sleep...\n";
		sleep(3);
	}
?>
