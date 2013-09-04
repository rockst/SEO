<?php
	// 廠商活動訊息：完全資料收集版
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/classified/prom.php?p=1', 'page'=>'p=33', 'pattern'=>'a.link7'),
	);
	getURL($rows);
?>
