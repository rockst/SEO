<?php
	include(dirname(__FILE__) . "/.library.php");
	include(dirname(__FILE__) . "/.config.php");
	include(dirname(__FILE__) . "/.account.php");
	include(dirname(__FILE__) . "/simple_html_dom.php");

	$page_limit = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 3;
	$rows = array();
	$urls = get_url_list("http://verywed.com/forum/wedlife/list/{page}.html", "td.subject a[href$=-1.html]", $page_limit);

	foreach($urls as $i=>$url) {

		echo ($i + 1) . "- " . $url . "\n";

		if(!preg_match("/([0-9]+)-1.html$/i", $url, $matchs) || empty($matchs[1])) {
			echo "- URL format is fail\n";
			continue;
		}

		$id = $matchs[1];

		$rows[$i]["url"] = $url . "?utm_source=yahoo&utm_medium=ugc";
		$rows[$i]["thread_url"] = $rows[$i]["url"];
		$rows[$i]["page_no"] = 1;
		$rows[$i]["content_type"] = "forum";
		$rows[$i]["language"] = "zh-Hant";
		$rows[$i]["region"] = "TW";
		$rows[$i]["site_name"] = "verywed.com";
		$rows[$i]["category"] = "婚後";

		$html = file_get_html($url);

		// title
		foreach($html->find("div#post_" . $id . " div.subject h4") as $element) {
			$rows[$i]["title"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		if(empty($rows[$i]["title"])) { unset($rows[$i]); echo "- title is empty\n"; continue; }

		// body
		foreach($html->find("div#post_" . $id . " div.dfs") as $element) {
			$rows[$i]["body"] = preg_replace("/\s+/u", "", html2text($element->plaintext));
			break;
		}
		// if(empty($rows[$i]["body"])) { unset($rows[$i]); echo "- body is empty\n"; continue; }
		if(empty($rows[$i]["body"])) { $rows[$i]["body"] = $rows[$i]["title"]; }

		// created_time and updated_time
		foreach($html->find("div#post_" . $id . " p.created") as $element) {
			if(preg_match_all("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $element->plaintext, $matches) && !empty($matches[0][0])) {
				$D = new DateTime(trim($matches[0][0]));
				$rows[$i]["created_time"] = $D->getTimestamp();
				$rows[$i]["updated_time"] = $rows[$i]["created_time"]; 
			}
			break;
		}
		if(empty($rows[$i]["created_time"])) { unset($rows[$i]); echo "- created_time is empty\n"; continue; }
		if(empty($rows[$i]["updated_time"])) { unset($rows[$i]); echo "- updated_time is empty\n"; continue; }

		// creator
		foreach($html->find("div#post_" . $id . " a.user_name") as $element) {
			$rows[$i]["creator"] = html2text($element->plaintext);
			break;
		}

		// no_of_replies and no_of_views
		foreach($html->find("div#post_" . $id . " a[href^=http://verywed.com/forum/userThread/member/]") as $element) {
			$html2 = file_get_html($element->href);
			$temp = array();
			$key  = "";
			foreach($html2->find("td.subject a") as $j=>$element) {
				$temp[$j]["href"] = $element->href;
				if(preg_match("/" . $id . "-[0-9]+.html$/", $element->href)) {
					$key = $j;
				}
			}
			foreach($html2->find("td.hitAndReplyCount") as $k=>$element) {
				$temp[$k]["hitAndReplyCount"] = $element->plaintext;
			}
			if(!empty($temp[$key]["hitAndReplyCount"])) {
				list($rows[$i]["no_of_replies"], $rows[$i]["no_of_views"]) = explode("/", $temp[$key]["hitAndReplyCount"]);
			}
			unset($html2);
			break;
		}

		// tag
		$rows[$i]["tag"] = array();
		$limit = 4;
		foreach($html->find("div#post_" . $id . " div.tag a") as $j=>$element) {
			array_push($rows[$i]["tag"], trim($element->plaintext));
			if(($j + 1) == $limit) { break; }
		}

		// author_thumb
		foreach($html->find("div#post_" . $id . " img.user_cover") as $element) {
			if(preg_match("/^http:\/\/s.verywed.com/i", $element->src)) {
				$rows[$i]["author_thumb"] = trim($element->src);
			}
			break;
		}

		// image
		$rows[$i]["image"] = array();
		$limit = 4;
		foreach($html->find("div#post_" . $id . " div.dfs img[src^=http://s.verywed.com/s1]") as $j=>$element) {
			array_push($rows[$i]["image"], $element->src);
			if(($j + 1) == $limit) { break; }
		}
	}

	$filename = "tw_verywed_" . date("Ymd_His") . "_2";
	buildXML($filename, $rows);
?>
