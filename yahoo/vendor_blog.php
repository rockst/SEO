<?php
	include(dirname(__FILE__) . "/.library.php");
	include(dirname(__FILE__) . "/.config.php");
	include(dirname(__FILE__) . "/.account.php");
	include(dirname(__FILE__) . "/simple_html_dom.php");

	$page_limit = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 5;
	$rows = array();
	$urls = get_url_list("http://verywed.com/classified/blog.php?p={page}", "div#list_box_text a.link-71", $page_limit);

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
		foreach($html->find("div#banner a.url") as $element) {
			$rows[$i]["blog_url"] = $element->href . "/blog?utm_source=yahoo&utm_medium=ugc";
			break;
		}
		if(empty($rows[$i]["blog_url"])) { unset($rows[$i]); echo "- blog_url is empty\n"; continue; }

		// title
		foreach($html->find("div.title a.link") as $element) {
			$rows[$i]["title"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		if(empty($rows[$i]["title"])) { unset($rows[$i]); echo "- title is empty\n"; continue; }

		// body
		foreach($html->find("div.blog_content td.word_break") as $element) {
			$rows[$i]["body"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		// if(empty($rows[$i]["body"])) { unset($rows[$i]); echo "- body is empty\n"; continue; }
		if(empty($rows[$i]["body"])) { $rows[$i]["body"] = $rows[$i]["title"]; }

		// creator & blog_title 
		foreach($html->find("div#banner div.name") as $element) {
			$rows[$i]["creator"] = trim($element->plaintext);
			$rows[$i]["blog_title"] = $rows[$i]["creator"] . "的部落格";
			break;
		}
		if(empty($rows[$i]["blog_title"])) { unset($rows[$i]); echo "- blog_title is empty\n"; continue; }

		// created_time and updated_time
		foreach($html->find("div.blog_content") as $element) {
			$text = html2text($element->plaintext);
			if(preg_match("/(\d{4})\.(\d{2})\.(\d{2}) - (\d{2}):(\d{2}) (AM|PM)+/", $text, $matchs)) {
				$date = $matchs[1] . "-" . $matchs[2] . "-" . $matchs[3] . " " . (($matchs[6] == "PM") ? ((int)$matchs[4] + 12) : $matchs[4]) . ":" . $matchs[5] . ":00";
				$D = new DateTime($date);
				$rows[$i]["created_time"] = $D->getTimestamp();
				$rows[$i]["updated_time"] = $rows[$i]["created_time"]; 
			}
			break;
		}
		if(empty($rows[$i]["created_time"])) { unset($rows[$i]); echo "- created_time is empty\n"; continue; }
		if(empty($rows[$i]["updated_time"])) { unset($rows[$i]); echo "- updated_time is empty\n"; continue; }

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

	$filename = "tw_verywed_" . date("Ymd_His") . "_4";
	buildXML($filename, $rows);

?>
