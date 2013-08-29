<?php
	include('simple_html_dom.php');
	$url = "http://verywed.com/classified/album.php?p=1";
	// for($i = 1; $i <= 1195; $i++) {
	for($i = 1; $i <= 3; $i++) {
		$html = file_get_html(preg_replace("/p=[0-9]+/i", "p=" . $i, $url));
		foreach($html->find('td.photo-m a') as $element) { 
			echo preg_replace("/http:\/\/verywed.com\/[0-9]+\/album\/([a-zA-Z0-9]+)$/i", "http://verywed.com/vendor/album/album.php?key=\$1", makURL("http://verywed.com/", $element->href)) . "\n";
			unset($element);
		}
		unset($html);
		sleep(1);
	}
?>
