<?php
	include(dirname(__FILE__) . "/.library.php");
	include(dirname(__FILE__) . "/.config.php");
	include(dirname(__FILE__) . "/.account.php");
	include(dirname(__FILE__) . "/simple_html_dom.php");

	$page_limit = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 3;
	$rows = array();
	$urls = get_url_list("http://verywed.com/vwblog/channel/wedding?p={page}", "div#articles div.excerpt h5 a", $page_limit);

	foreach($urls as $i=>$url) {

		echo ($i + 1) . "- " . $url . "\n";

		if(!preg_match("/^http:\/\/verywed.com\/vwblog\/(.*)\/article\/[0-9]+/i", $url, $matchs) || empty($matchs[1])) {
			echo "- URL format is fail\n";
			continue;
		}

		$rows[$i]["url"] = $url . "?utm_source=yahoo&utm_medium=ugc";
		$rows[$i]["blog_url"] = "http://verywed.com/vwblog/" . $matchs[1] . "/?utm_source=yahoo&utm_medium=ugc";
		$rows[$i]["page_no"] = 1;
		$rows[$i]["content_type"] = "blog";
		$rows[$i]["language"] = "zh-Hant";
		$rows[$i]["region"] = "TW";
		$rows[$i]["site_name"] = "verywed.com";
		$rows[$i]["category"] = "婚禮";

		$html = file_get_html($url);

		// title
		foreach($html->find("li.article-title a") as $element) {
			$rows[$i]["title"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		if(empty($rows[$i]["title"])) { unset($rows[$i]); echo "- title is empty\n"; continue; }

		// body
		foreach($html->find("div.article-body") as $element) {
			$rows[$i]["body"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		// if(empty($rows[$i]["body"])) { unset($rows[$i]); echo "- body is empty\n"; continue; }
		if(empty($rows[$i]["body"])) { $rows[$i]["body"] = $rows[$i]["title"]; }

		// blog_title 
		foreach($html->find("div#blogsub h2") as $element) {
			$rows[$i]["blog_title"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		if(empty($rows[$i]["blog_title"])) { unset($rows[$i]); echo "- blog_title is empty\n"; continue; }

		// created_time and updated_time
		foreach($html->find("li.article-date") as $element) {
			$text = html2text($element->plaintext);
			$date = preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) \d{2}:\d{2} (AM|PM)+/", "\$1-\$2-\$3", $text);
			$type = preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$6", $text);
			if($type == "PM") {
				$time = (int) preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$4", $text) + 12;
				$time.= ":" . preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$5:00", $text);
			} else {
				$time = preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$4:\$5:00", $text);
			}
			$D = new DateTime($date . " " . $time);
			$rows[$i]["created_time"] = $D->getTimestamp();
			$rows[$i]["updated_time"] = $rows[$i]["created_time"]; 
			break;
		}
		if(empty($rows[$i]["created_time"])) { unset($rows[$i]); echo "- created_time is empty\n"; continue; }
		if(empty($rows[$i]["updated_time"])) { unset($rows[$i]); echo "- updated_time is empty\n"; continue; }

		// creator
		$rows[$i]["creator"] = preg_replace("/^http:\/\/verywed.com\/vwblog\/(.*)\/article\/\d+/i", "\$1", $url); 

		// tag
		$rows[$i]["tag"] = array();
		$limit = 4;
		foreach($html->find("li.note-title") as $j=>$element) {
			array_push($rows[$i]["tag"], trim($element->plaintext));
			if(($j + 1) == $limit) { break; }
		}

		// author_thumb
		foreach($html->find("div.avatar a img") as $element) {
			if(preg_match("/^http:\/\/s.verywed.com/i", $element->src)) {
				$rows[$i]["author_thumb"] = trim($element->src);
			}
			break;
		}

		// image
		$rows[$i]["image"] = array();
		$limit = 4;
		foreach($html->find("div.article-body img[src^=http://s.verywed.com/s1]") as $j=>$element) {
			array_push($rows[$i]["image"], $element->src);
			if(($j + 1) == $limit) { break; }
		}
	}

	$filename = "tw_verywed_" . date("Ymd_His") . "_3";
	buildXML($filename, $rows);

?>
