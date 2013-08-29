<?php
	include('simple_html_dom.php');

	$rows = array(
		array('url'=>'http://verywed.com/album/hot_albums?p=1', 'page'=>'p=3', 'pattern'=>'div.title a'),
		array('url'=>'http://verywed.com/album/new_albums?p=1', 'page'=>'p=3', 'pattern'=>'div.title a'),
		array('url'=>'http://verywed.com/album/hot_videos?p=1', 'page'=>'p=1', 'pattern'=>'div.title a'),
		array('url'=>'http://verywed.com/album/new_videos?p=1', 'page'=>'p=2', 'pattern'=>'div.title a'),
		array('url'=>'http://verywed.com/album/collect/1?p=1', 'page'=>'p=5', 'pattern'=>'div.img a[rel=external]'),
		array('url'=>'http://verywed.com/album/collect/104?p=1', 'page'=>'p=5', 'pattern'=>'div.img a[rel=external]'),
		array('url'=>'http://verywed.com/album/biz?p=1', 'page'=>'p=5', 'pattern'=>'div.album a[rel=external]'),
		array('url'=>'http://verywed.com/album/collect/91?p=1', 'page'=>'p=5', 'pattern'=>'div.img a[rel=external]'),
		array('url'=>'http://verywed.com/album/collect/96?p=1', 'page'=>'p=5', 'pattern'=>'div.img a[rel=external]'),
		array('url'=>'http://verywed.com/album/collect/104?p=1', 'page'=>'p=5', 'pattern'=>'div.img a[rel=external]')
	);
	
	getURL($rows);
?>
