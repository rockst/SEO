<?php
	// 宴客經驗談
	include('simple_html_dom.php');
	$count = 15; // 一頁幾筆
	$limit = 10; // 共幾頁

	// 從首頁地區開始找
	$html = file_get_html('http://verywed.com/resEvaluate/');
	foreach($html->find('tr td font.size10 a') as $element) { // 地區
		$city = 'http://verywed.com/resEvaluate/' . $element->href;
		unset($element);
		echo $city . "\n";
		$city .= "&fp=0";
		for($i = 0; $i <= (($limit - 1) * $count); $i += $count) { // 經驗談
			$html = file_get_html(preg_replace("/fp=[0-9]+/i", "fp=" . $i, $city));
			foreach($html->find('tr td font[color=F34646] a') as $element) { 
				echo 'http://verywed.com/resEvaluate/' . $element->href . "\n";
				unset($element);
			}
			unset($html);
			sleep(1);
		}
		sleep(1);
	}
	unset($html);

	// 取得廠商的宴客經驗談
	$html = file_get_html('http://verywed.com/resEvaluate/classified.php');
	foreach($html->find('a[href^=/resEvaluate/lstRestaurant.php?ven=]') as $element) {
		$vendor = 'http://verywed.com' . $element->href;
		echo $vendor . "\n";
		$vendor .= "&fp=0";
		unset($element);
		for($i = 0; $i <= (($limit - 1) * $count); $i += $count) { // 經驗談
			$html = file_get_html(preg_replace("/fp=[0-9]+/i", "fp=" . $i, $vendor));
			foreach($html->find('tr td font[color=F34646] a') as $element) { 
				echo 'http://verywed.com/resEvaluate/' . $element->href . "\n";
				unset($element);
			}
			unset($html);
		}
		sleep(1);
	}
	unset($html);
?>
