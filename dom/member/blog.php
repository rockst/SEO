<?php
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/vwblog/?p=1', 'page'=>'p=10', 'pattern'=>'div#articles div.wrap div.box div.avatar a'),
		array('url'=>'http://verywed.com/vwblog/?p=1', 'page'=>'p=10', 'pattern'=>'div#articles div.wrap div.excerpt h5 a'),
		array('url'=>'http://verywed.com/vwblog/?p=1', 'page'=>'p=10', 'pattern'=>'div#new-memos div.wrap div.box div.title a'),
		array('url'=>'http://verywed.com/vwblog/channel/wedding?p=1', 'page'=>'p=5', 'pattern'=>'div.box div.excerpt h5 a'),
		array('url'=>'http://verywed.com/vwblog/channel/wedlife?p=1', 'page'=>'p=5', 'pattern'=>'div.box div.excerpt h5 a'),
		array('url'=>'http://verywed.com/vwblog/channel/baby?p=1', 'page'=>'p=5', 'pattern'=>'div.box div.excerpt h5 a'),
		array('url'=>'http://verywed.com/vwblog/channel/food?p=1', 'page'=>'p=5', 'pattern'=>'div.box div.excerpt h5 a'),
		array('url'=>'http://verywed.com/vwblog/channel/travel?p=1', 'page'=>'p=5', 'pattern'=>'div.box div.excerpt h5 a')
	);
	getURL($rows);
?>
