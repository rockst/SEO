<?php
	// 討論區：每日版
	include(dirname(__FILE__) . "/simple_html_dom.php");
	$rows = array(
		// 結婚經驗交流
		array('url'=>'http://verywed.com/forum/expexch/list/1.html', 'page'=>'3.html', 'pattern'=>'table tr td.subject a'),
		// 婚後生活
		array('url'=>'http://verywed.com/forum/wedlife/list/1.html', 'page'=>'3.html', 'pattern'=>'table tr td.subject a'),
		// 交易合購
		array('url'=>'http://verywed.com/forum/trade/list/1.html', 'page'=>'2.html', 'pattern'=>'table tr td.subject a'),
		// 旅遊
		array('url'=>'http://verywed.com/forum/travelcomp/list/1.html','page'=>'1.html', 'pattern'=>'table tr td.subject a'),
		// 徵求廠商
		array('url'=>'http://verywed.com/forum/look4m/list/1.html', 'page'=>'2.html', 'pattern'=>'table tr td.subject a'),
		// 廠商討論
		array('url'=>'http://verywed.com/forum/mtalks/list/1.html', 'page'=>'2.html', 'pattern'=>'table tr td.subject a'),
		// 精華文章
		array('url'=>'http://verywed.com/forum/essence/list/1.html', 'page'=>'1.html', 'pattern'=>'table tr td.subject a'),
		// 香港
		array('url'=>'http://verywed.com/forum/hongkong/list/1.html', 'page'=>'1.html', 'pattern'=>'table tr td.subject a'),
	);
	getURL($rows);
?>
