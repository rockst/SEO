<?php
	// 婚紗經驗談：每日版
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$html = file_get_html('http://verywed.com/evaluate/');
	foreach($html->find('a[href^=/evaluate/shops]') as $element) { // 公司
		$url = 'http://verywed.com' . $element->href;  
		$html = file_get_html($url);
		foreach($html->find('div.table_list_left a') as $element) { // 經驗談
			echo 'http://verywed.com' . $element->href . "\n";
			unset($element);
		}
		unset($element);
		unset($html);
		sleep(1);
	}
	unset($element);
?>
