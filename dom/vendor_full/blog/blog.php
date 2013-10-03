<?php
	// 廠商部落格：完全資料收集版
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$rows = array(
		array('url'=>'http://verywed.com/classified/blog.php?p=1', 'page'=>'p=837', 'pattern'=>'a.link-71'),
	);
	getURL($rows);
?>
