<?php
	// 會員部落格
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$rows = array(
		array('url'=>'http://verywed.com/vwblog/?p=1', 'page'=>'p=100', 'pattern'=>'div#articles div.wrap div.box div.avatar a'), // 會員部落格
		array('url'=>'http://verywed.com/vwblog/?p=1', 'page'=>'p=1000', 'pattern'=>'div#articles div.wrap div.excerpt h5 a'), // 最新文章
		array('url'=>'http://verywed.com/vwblog/?p=1', 'page'=>'p=100', 'pattern'=>'div#new-memos div.wrap div.box div.title a'), // 最新便利貼
		array('url'=>'http://verywed.com/vwblog/channel/wedding?p=1', 'page'=>'p=100', 'pattern'=>'div.box div.excerpt h5 a'), // 最新婚禮日記簿 
		array('url'=>'http://verywed.com/vwblog/channel/wedlife?p=1', 'page'=>'p=100', 'pattern'=>'div.box div.excerpt h5 a'), // 最新婚後生活
		array('url'=>'http://verywed.com/vwblog/channel/baby?p=1', 'page'=>'p=100', 'pattern'=>'div.box div.excerpt h5 a'), // 最新寶寶生活
		array('url'=>'http://verywed.com/vwblog/channel/food?p=1', 'page'=>'p=100', 'pattern'=>'div.box div.excerpt h5 a'), // 最新料理食記
		array('url'=>'http://verywed.com/vwblog/channel/travel?p=1', 'page'=>'p=100', 'pattern'=>'div.box div.excerpt h5 a'), // 最新旅遊手扎
	);
	getURL($rows);
?>
