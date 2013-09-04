<?php
	// 廠商部落格：每日版
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/classified/blog.php?p=1', 'page'=>'p=3', 'pattern'=>'a.link-71'),
	);
	getURL($rows);
?>
