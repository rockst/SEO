<?php
include(dirname(__FILE__) . "/.library.php");
include(dirname(__FILE__) . "/.config.php");
include(dirname(__FILE__) . "/simple_html_dom.php");

$rows = array();


$urls = array();
for($p = 0; $p <= 1; $p++) {
	$list = "http://verywed.com/magazine/?p=" . $p;
	$html = file_get_html($list);
	foreach($html->find("ul#articleList h4 a") as $i=>$element) {
		$urls[] = $element->href;
	}
}

foreach($urls as $i=>$url) {
	echo ($i + 1) . "- " . $url . "\n";
	$rows[$i]["url"] = $url . "?utm_source=yahoo&utm_medium=ugc";
	$rows[$i]["blog_url"] = "http://verywed.com/magazine/?utm_source=yahoo&utm_medium=ugc";
	$rows[$i]["page_no"] = preg_replace("/^http:\/\/verywed.com\/magazine\/vo([0-9]+).html$/i", "\$1", $url);
	$rows[$i]["content_type"] = "blog";
	$rows[$i]["language"] = "zh-Hant";
	$rows[$i]["region"] = "TW";
	$rows[$i]["site_name"] = "verywed.com";
	$rows[$i]["category"] = "婚禮";

	$html = file_get_html($url);

	// title
	foreach($html->find("div#volneme h1") as $element) {
		$rows[$i]["title"] = trim(html2text($element->plaintext));
		break;
	}
	// body
	foreach($html->find("div.article-body") as $element) {
		$rows[$i]["body"] = trim(html2text($element->plaintext));
		break;
	}
print_r($rows); exit();
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
buildXML("vw_20131004_0.xml", $rows, true);
?>
