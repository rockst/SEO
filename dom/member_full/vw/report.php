<?php
	// 自制內容
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$rows = array(
		// SNG 報導
		array('url'=>'http://verywed.com/vwblog/veryWedPR?p=1', 			 'page'=>'p=6', 	'pattern'=>'li.article-title a'),
		// 新婚誌
		array('url'=>'http://verywed.com/magazine/?p=1', 					 'page'=>'p=10', 	'pattern'=>'ul#articleList div.cover a'),
		// 婚後誌
		array('url'=>'http://verywed.com/vwblog/veryWedHappyHome/?p=1', 'page'=>'p=30', 	'pattern'=>'li.article-title a'),
	);
	getURL($rows);
?>
