<?php
	// 家族:每日版
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/family/?o=hot&p=1', 'page'=>'p=1', 'pattern'=>'div.img a'),
	);
	getURL($rows);
?>
