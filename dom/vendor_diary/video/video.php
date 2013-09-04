<?php
	// 廠商影片：每日版
	include('simple_html_dom.php');
	$url = "http://verywed.com/classified/video.php?p=1";
	for($i = 1; $i <= 2; $i++) {
		$html = file_get_html(preg_replace("/p=[0-9]+/i", "p=" . $i, $url));
		foreach($html->find('div.videoTV a') as $element) { 
			echo preg_replace("/http:\/\/verywed.com\/[0-9]+\/video\/([a-zA-Z0-9]+)$/i", "http://verywed.com/vendor/video/video.php?key=\$1", makURL("http://verywed.com/", $element->href)) . "\n";
			unset($element);
		}
		unset($html);
		sleep(1);
	}
?>
