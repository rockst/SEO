<?php
	include('simple_html_dom.php');

	$rows = array(
		// array('url'=>'http://verywed.com/classified/blog.php?p=1', 'page'=>'p=837', 'pattern'=>'a.link-71'),
		array('url'=>'http://verywed.com/classified/blog.php?p=1', 'page'=>'p=3', 'pattern'=>'a.link-71'),
	);
	
	getURL($rows);
?>
