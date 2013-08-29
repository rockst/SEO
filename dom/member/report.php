<?php
	include('simple_html_dom.php');

	$rows = array(
		array('url'=>'http://verywed.com/vwblog/veryWedPR?p=1', 'page'=>'p=6', 'pattern'=>'li.article-title a'),
		array('url'=>'http://verywed.com/magazine/?p=1', 'page'=>'p=10', 'pattern'=>'ul#articleList div.cover a'),
		array('url'=>'http://verywed.com/vwblog/veryWedHappyHome/?p=1', 'page'=>'p=30', 'pattern'=>'li.article-title a'),
	);
	
	getURL($rows);
?>
