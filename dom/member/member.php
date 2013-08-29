<?php
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/forum/expexch/list/1.html', 'page'=>'5.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/wedlife/list/1.html', 'page'=>'5.html', 'pattern'=>'a.memlink')
	);
	foreach($rows as $row) {
		$urls = array();
		buildPageURL($urls, $row['url'], $row['pattern'], $row['page']);
		foreach($urls as $url) {
			$html = file_get_html($url[0]);
			foreach($html->find($url[1]) as $element) {
				echo preg_replace("/http:\/\/my\.verywed.com\/(.{1,})$/i", "http://verywed.com/my/\$1", $element->href) . "\n";
				unset($element);
			}
			sleep(1);
			unset($html);
		}
	}
?>
