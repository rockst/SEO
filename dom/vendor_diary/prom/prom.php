<?php
	// 廠商活動訊息：每日版
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$rows = array(
		array('url'=>'http://verywed.com/classified/prom.php?p=1', 'page'=>'p=3', 'pattern'=>'a.link7'),
	);
	getURL($rows);
?>
