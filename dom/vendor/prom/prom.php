<?php
	include('simple_html_dom.php');

	$rows = array(
		// array('url'=>'http://verywed.com/classified/prom.php?p=1', 'page'=>'p=33', 'pattern'=>'a.link7'),
		array('url'=>'http://verywed.com/classified/prom.php?p=1', 'page'=>'p=3', 'pattern'=>'a.link7'),
	);
	
	getURL($rows);
?>
