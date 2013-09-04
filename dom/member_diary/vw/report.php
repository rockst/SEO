<?php
	// 自制內容：每日版
	include('simple_html_dom.php');
	$rows = array(
		// SNG 報導
		array('url'=>'http://verywed.com/vwblog/veryWedPR?p=1', 			 'page'=>'p=1', 	'pattern'=>'li.article-title a'),
		// 新婚誌
		array('url'=>'http://verywed.com/magazine/?p=1', 					 'page'=>'p=1', 	'pattern'=>'ul#articleList div.cover a'),
		// 婚後誌
		array('url'=>'http://verywed.com/vwblog/veryWedHappyHome/?p=1', 'page'=>'p=1', 	'pattern'=>'li.article-title a'),
	);
	getURL($rows);
?>
