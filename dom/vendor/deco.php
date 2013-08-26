<?php
	include('simple_html_dom.php');
	$html = file_get_html('http://verywed.com/deco/list-florist.php');
	foreach($html->find('div.makeupContent table a') as $element) {
		if(preg_match("/[0-9]{8,}/i", $element->href, $matches) && isset($matches[0])) {
			echo "http://verywed.com/vendor/key.php?key=" . $matches[0] . "\n";
			$homepage = "http://verywed.com/" . $matches[0] . "/deco";
			$html = file_get_html($homepage);
			foreach($html->find('div#nav-main a.button') as $element) {
				echo makURL("http://verywed.com/deco/", $element->href) . "\n";
				unset($element);
			}
			unset($html);
		}
		unset($element);
		sleep(1);
	}
	unset($html);
?>
