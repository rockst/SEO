<?php
include(dirname(__FILE__) . "/.library.php");
include(dirname(__FILE__) . "/.config.php");
include(dirname(__FILE__) . "/simple_html_dom.php");

$rows = array();
$urls = array();

for($p = 1; $p <= 10; $p++) {
	$list = "http://verywed.com/classified/blog.php?p=" . $p;
	$html = file_get_html($list);
	foreach($html->find("div#list_box_text a.link-71") as $i=>$element) {
		$urls[] = "http://verywed.com" . $element->href;
	}
}

foreach($urls as $i=>$url) {
	echo ($i + 1) . "- " . $url . "\n";

	$rows[$i]["url"] = $url . "?utm_source=yahoo&utm_medium=ugc";
	$rows[$i]["page_no"] = 1;
	$rows[$i]["content_type"] = "blog";
	$rows[$i]["language"] = "zh-Hant";
	$rows[$i]["region"] = "TW";
	$rows[$i]["site_name"] = "verywed.com";
	$rows[$i]["category"] = "婚禮";

	$html = file_get_html($url);

	// blog_url
	foreach($html->find("div#nav-main a.button2") as $element) {
		$rows[$i]["blog_url"] = $element->href . "?utm_source=yahoo&utm_medium=ugc";
		break;
	}

	// title
	foreach($html->find("div.title a.link") as $element) {
		$rows[$i]["title"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
		break;
	}

	// body
	foreach($html->find("div.blog_content td.word_break") as $element) {
		$rows[$i]["body"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
		break;
	}

	// creator & blog_title 
	foreach($html->find("div#banner div.name") as $element) {
		$rows[$i]["creator"] = trim($element->plaintext);
		$rows[$i]["blog_title"] = $rows[$i]["creator"] . "的部落格";
		break;
	}

	// created_time and updated_time
	foreach($html->find("div.blog_content div[align=right].size13") as $element) {
		$text = trim(html2text($element->plaintext));
		if(preg_match("/(\d{4})\.(\d{2})\.(\d{2}) - (\d{2}):(\d{2}) (AM|PM)+/", $text, $matchs)) {
			$date = $matchs[1] . "-" . $matchs[2] . "-" . $matchs[3] . " " . (($matchs[6] == "PM") ? ((int)$matchs[4] + 12) : $matchs[4]) . ":" . $matchs[5] . ":00";
			$D = new DateTime($date);
			$rows[$i]["created_time"] = $D->getTimestamp();
			$rows[$i]["updated_time"] = $rows[$i]["created_time"]; 
		} else {
			echo "- Can`t find created datetime\n";
		}
		break;
	}
	// author_thumb
	if(preg_match("/^http:\/\/verywed.com\/([0-9]+)\/blog/i", $rows[$i]["blog_url"], $matchs) && !empty($matchs[1])) {
		$vendor_url = "http://verywed.com/vendor/key.php?key=" . $matchs[1];
		$html2 = file_get_html($vendor_url);
		foreach($html2->find("img[alt=" . $rows[$i]["creator"]."]") as $element) {
			$rows[$i]["author_thumb"] = trim($element->src);
			break;
		}
	}
	// image
	$rows[$i]["image"] = array();
	$limit = 4;
	foreach($html->find("div.blog_content td.word_break img[src^=http://s.verywed.com/s1]") as $j=>$element) {
		array_push($rows[$i]["image"], $element->src);
		if(($j + 1) == $limit) { break; }
	}
}
buildXML("vw_20131007_4.xml", $rows);
?>
