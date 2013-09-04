<?php
	// 相簿
	include('simple_html_dom.php');
	$rows = array(
		array('url'=>'http://verywed.com/album/hot_albums?p=1', 	'page'=>'p=1',  'pattern'=>'div.title a'), // 熱門相簿
		array('url'=>'http://verywed.com/album/new_albums?p=1', 	'page'=>'p=1',  'pattern'=>'div.title a'), // 最新相簿
		array('url'=>'http://verywed.com/album/hot_videos?p=1', 	'page'=>'p=1',  'pattern'=>'div.title a'), // 熱門影音
		array('url'=>'http://verywed.com/album/new_videos?p=1', 	'page'=>'p=1',  'pattern'=>'div.title a'), // 最新影音
		array('url'=>'http://verywed.com/album/collect/1?p=1', 	'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 婚禮相關
		array('url'=>'http://verywed.com/album/collect/104?p=1', 'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 旅遊相關
		array('url'=>'http://verywed.com/album/biz?p=1', 			'page'=>'p=10', 'pattern'=>'div.album a[rel=external]'), // 廠商相關
		array('url'=>'http://verywed.com/album/collect/24?p=1', 	'page'=>'p=10', 'pattern'=>'div.album a[rel=external]'), // 外拍相關
		array('url'=>'http://verywed.com/album/collect/13?p=1', 	'page'=>'p=10', 'pattern'=>'div.album a[rel=external]'), // 造型相關
		array('url'=>'http://verywed.com/album/collect/77?p=1', 	'page'=>'p=10', 'pattern'=>'div.album a[rel=external]'), // MV相關
		array('url'=>'http://verywed.com/album/collect/91?p=1', 	'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 婚後生活相關
		array('url'=>'http://verywed.com/album/collect/96?p=1', 	'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 寶寶相關
		array('url'=>'http://verywed.com/album/collect/104?p=1', 'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 婚後旅遊相關
		array('url'=>'http://verywed.com/album/collect/93?p=1', 	'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 全家福 
		array('url'=>'http://verywed.com/album/collect/92?p=1', 	'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 孕媽媽 
		array('url'=>'http://verywed.com/album/collect/98?p=1', 	'page'=>'p=10', 'pattern'=>'div.img a[rel=external]'), // 寶寶 
		array('url'=>'http://verywed.com/album/collect/102?p=1', 'page'=>'p=1',  'pattern'=>'div.img a[rel=external]'), // 寶寶成長 
	);
	// 取得熱門關鍵字
	$html = file_get_html('http://verywed.com/album/');
	foreach($html->find('a[href^=/album/search?k=]') as $element) {	
		$url = "http://verywed.com" . $element->href;
		echo $url . "\n";
		unset($element);
		array_push($rows, array("url"=>$url . "?p=1", "page"=>"p=10", "pattern"=>"div.img a[rel=external],div.name a"));
	}
	// END;
	// 取得首頁輪替照片的標籤網頁
	$urls = array();
	buildPageURL($urls, 'http://verywed.com/album/?p=1', 'div#rolling-left a', 'p=10'); // 產生分頁網址
	$urls = array_unique(fetchURL($urls, true));
	foreach($urls as $url) {
		array_push($rows, array("url"=>$url . "?p=1", "page"=>"p=10", "pattern"=>"div.img a[rel=external]"));
	}
	unset($urls);
	// END;
	getURL($rows);
?>
