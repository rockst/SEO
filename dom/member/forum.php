<?php
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/forum/expexch/list/1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/1.html', 'page'=>'5.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/1.html', 'page'=>'5.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/1.html', 'page'=>'5.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/1.html', 'page'=>'5.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/1.html', 'page'=>'3.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/1.html', 'page'=>'3.html', 'pattern'=>'table tr td.subject a')
	);
	getURL($rows);
?>
