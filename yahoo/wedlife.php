<?php
include(dirname(__FILE__) . "/.library.php");
include(dirname(__FILE__) . "/.config.php");
include(dirname(__FILE__) . "/simple_html_dom.php");

$rows = array();

$list = "http://verywed.com/forum/wedlife";
$html = file_get_html($list);
foreach($html->find("td.subject a") as $i=>$element) {
	if($i == 10) break;
	$urls[] = $element->href;
}

foreach($urls as $i=>$url) {
	echo ($i + 1) . "- " . $url . "\n";
	if(!preg_match("/([0-9]+)-[0-9]+.html/", $url, $matchs) || empty($matchs[1])) {
		exit();
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
		$rows[$i]["title"] = trim(html2text($element->plaintext));
		break;
	}
	// body
	foreach($html->find("div#post_" . $id . " div.dfs") as $element) {
		$rows[$i]["body"] = trim(html2text($element->plaintext));
		break;
	}
	// created_time and updated_time
	foreach($html->find("div#post_" . $id . " p.created") as $element) {
		if(preg_match_all("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $element->plaintext, $matches) && !empty($matches[0][0])) {
			$D = new DateTime(trim($matches[0][0]));
			$rows[$i]["created_time"] = $D->getTimestamp();
			$rows[$i]["updated_time"] = $rows[$i]["created_time"]; 
		}
		break;
	}
	// creator
	foreach($html->find("div#post_" . $id . " a.user_name") as $element) {
		$rows[$i]["creator"] = trim(html2text($element->plaintext));
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
		list($rows[$i]["no_of_replies"], $rows[$i]["no_of_views"]) = explode("/", $temp[$key]["hitAndReplyCount"]);
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
buildXML("vw_20131004_0.xml", $rows, true);
?>
