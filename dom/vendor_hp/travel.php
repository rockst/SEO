<?php
	// 旅遊服務
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$html = file_get_html('http://verywed.com/classified/list.php?thread=47');
	foreach($html->find('div.list_vendor_box_col a.textcolor2') as $element) {
		if(preg_match("/[0-9]{8,}/i", $element->href, $matches) && isset($matches[0])) {
			echo "http://verywed.com/vendor/key.php?key=" . $matches[0] . "\n";
			$homepage = "http://verywed.com/" . $matches[0] . "/classified";
			$html = file_get_html($homepage);
			foreach($html->find('div#nav-main a.button') as $element) {
				echo makURL("http://verywed.com/classified/", $element->href) . "\n";
				unset($element);
			}
			unset($html);
		}
		unset($element);
		sleep(1);
	}
	unset($html);
?>
