<?php
	include('simple_html_dom.php');

	// 婚紗經驗談
	$html = file_get_html('http://verywed.com/evaluate/');
	foreach($html->find('div.arealist td a') as $element) { // 地區
		$url = 'http://verywed.com' . $element->href;  
		// echo $url . "\n";
		$html = file_get_html($url);
		foreach($html->find('td.eva_listimg a,p.eva_morelist_text a') as $element) { // 公司
			$url = 'http://verywed.com' . $element->href;  
			// echo $url . "\n";
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

	// 宴客經驗談
	$count = 15; $limit = 5;
	$html = file_get_html('http://verywed.com/resEvaluate/');
	foreach($html->find('tr td font.size10 a') as $element) { // 地區
		$url = 'http://verywed.com/resEvaluate/' . $element->href . "&fp=0";
		for($i = 0; $i <= (($limit - 1) * $count); $i += $count) { // 經驗談
			$city = preg_replace("/fp=[0-9]+/i", "fp=" . $i, $url);
			// echo $city . "\n";
			$html = file_get_html($city);
			foreach($html->find('tr td font[color=F34646] a') as $element) { 
				echo 'http://verywed.com/resEvaluate/' . $element->href . "\n";
				unset($element);
			}
			unset($html);
			sleep(1);
		}
		unset($element);
		sleep(1);
	}
	unset($html);
?>
