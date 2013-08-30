<?php
	// 婚紗經驗談
	include('simple_html_dom.php');
	$html = file_get_html('http://verywed.com/evaluate/');
	foreach($html->find('div.arealist td a') as $element) { // 地區
		$url = 'http://verywed.com' . $element->href;  
		echo $url . "\n";
		$html = file_get_html($url);
		foreach($html->find('td.eva_listimg a,p.eva_morelist_text a') as $element) { // 公司
			$url = 'http://verywed.com' . $element->href;  
			echo $url . "\n";
			$html = file_get_html($url);
			foreach($html->find('div.table_list_left a') as $element) { // 經驗談
				echo $url = 'http://verywed.com' . $element->href . "\n";
				unset($element);
			}
			unset($element);
			unset($html);
			sleep(1);
		}
		unset($element);
		unset($html);
		sleep(1);
	}
	unset($html);
?>
