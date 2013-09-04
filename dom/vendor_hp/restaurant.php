<?php
	// 宴客場地
	include('simple_html_dom.php');
	$html = file_get_html('http://verywed.com/restaurant/list.php');
	foreach($html->find('div.makeupContent table a') as $element) {
		if(preg_match("/[0-9]{8,}/i", $element->href, $matches) && isset($matches[0])) {
			echo "http://verywed.com/vendor/key.php?key=" . $matches[0] . "\n";
			$homepage = "http://verywed.com/" . $matches[0] . "/restaurant";
			$html = file_get_html($homepage);
			foreach($html->find('div#nav-main a.button') as $element) {
				echo makURL("http://verywed.com/restaurant/", $element->href) . "\n";
				unset($element);
			}
			unset($html);
		}
		unset($element);
		sleep(1);
	}
	unset($html);
?>
