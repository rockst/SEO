<?php
	// 討論區
	include('simple_html_dom.php');
	$rows = array(
		// 結婚經驗交流
		array('url'=>'http://verywed.com/forum/expexch/list/1.html', 					'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/expexch/list/lastReply-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/expexch/list/hitCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/expexch/list/replyCount-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/expexch/list/bookmarkCount-1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/expexch/list/hasImage-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/expexch/list/hasFile-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 婚後生活
		array('url'=>'http://verywed.com/forum/wedlife/list/1.html', 					'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/lastReply-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/hitCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/replyCount-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/bookmarkCount-1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/hasImage-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/wedlife/list/hasFile-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 交易合購
		array('url'=>'http://verywed.com/forum/trade/list/1.html', 						'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/lastReply-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/hitCount-1.html', 			'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/replyCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/bookmarkCount-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/hasImage-1.html', 			'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/trade/list/hasFile-1.html', 			'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 旅遊
		array('url'=>'http://verywed.com/forum/travelcomp/list/1.html',				'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/lastReply-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/hitCount-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/replyCount-1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/bookmarkCount-1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/hasImage-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/travelcomp/list/hasFile-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 徵求廠商
		array('url'=>'http://verywed.com/forum/look4m/list/1.html', 					'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/lastReply-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/hitCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/replyCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/bookmarkCount-1.html',  'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/hasImage-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/look4m/list/hasFile-1.html', 			'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 廠商討論
		array('url'=>'http://verywed.com/forum/mtalks/list/1.html', 					'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/lastReply-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/hitCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/replyCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/bookmarkCount-1.html',  'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/hasImage-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/mtalks/list/hasFile-1.html', 			'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 精華文章
		array('url'=>'http://verywed.com/forum/essence/list/1.html', 					'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/lastReply-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/hitCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/replyCount-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/bookmarkCount-1.html', 'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/hasImage-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/essence/list/hasFile-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		// 香港
		array('url'=>'http://verywed.com/forum/hongkong/list/1.html', 					'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/lastReply-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/hitCount-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/replyCount-1.html', 	'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/bookmarkCount-1.html','page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/hasImage-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
		array('url'=>'http://verywed.com/forum/hongkong/list/hasFile-1.html', 		'page'=>'10.html', 'pattern'=>'table tr td.subject a'),
	);
	getURL($rows);
?>
