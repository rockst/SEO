<?php
	// 會員首頁
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/forum/expexch/list/1.html', 				'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/expexch/list/hitCount-1.html',   'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/expexch/list/replyCount-1.html', 'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/wedlife/list/1.html', 				'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/wedlife/list/hitCount-1.html',   'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/wedlife/list/replyCount-1.html', 'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/trade/list/1.html', 					'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/trade/list/hitCount-1.html',   	'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/trade/list/replyCount-1.html', 	'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/1.html', 			'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/hitCount-1.html','page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/replyCount-1.html', 'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/mtalks/list/1.html', 				'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/mtalks/list/hitCount-1.html',   	'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/mtalks/list/replyCount-1.html', 	'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/essence/list/1.html', 				'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/essence/list/hitCount-1.html',   'page'=>'3.html', 'pattern'=>'a.memlink'),
		array('url'=>'http://verywed.com/forum/essence/list/replyCount-1.html', 'page'=>'3.html', 'pattern'=>'a.memlink'),
	);
	// http://my.verywed.com/acount => http://verywed.com/my/account
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
		unset($urls);
	}
?>
