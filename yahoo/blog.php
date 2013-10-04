<?php
include(dirname(__FILE__) . "/.library.php");
include(dirname(__FILE__) . "/.config.php");
include(dirname(__FILE__) . "/simple_html_dom.php");

$rows = array();

$list = "http://verywed.com/vwblog/channel/wedding";
$html = file_get_html($list);
foreach($html->find("div#articles div.excerpt h5 a") as $i=>$element) {
	if($i == 10) break;
	$urls[] = $element->href;
}

foreach($urls as $i=>$url) {
	echo ($i + 1) . "- " . $url . "\n";
	if(preg_match("/^http:\/\/verywed.com\/vwblog\/.*\//i", $url, $matchs) && !empty($matchs[0])) {
		$rows[$i]["url"] = $url . "?utm_source=yahoo&utm_medium=ugc";
		$rows[$i]["blog_url"] = $matchs[0] . "?utm_source=yahoo&utm_medium=ugc";
		$rows[$i]["page_no"] = 1;
		$rows[$i]["content_type"] = "blog";
		$rows[$i]["language"] = "zh-Hant";
		$rows[$i]["region"] = "TW";
		$rows[$i]["site_name"] = "verywed.com";
		$rows[$i]["category"] = "婚禮";
		$html = file_get_html($url);
		// title
		foreach($html->find("li.article-title a") as $element) {
			$rows[$i]["title"] = trim(html2text($element->plaintext));
			break;
		}
		// body
		foreach($html->find("div.article-body") as $element) {
			$rows[$i]["body"] = trim(html2text($element->plaintext));
			break;
		}
		// blog_title 
		foreach($html->find("div#blogsub h2") as $element) {
			$rows[$i]["blog_title"] = trim(html2text($element->plaintext));
			break;
		}
		// created_time and updated_time
		foreach($html->find("li.article-date") as $element) {
			$text = trim(html2text($element->plaintext));
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
}
buildXML("vw_20131004_0.xml", $rows, true);
?>
