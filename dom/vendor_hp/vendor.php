<?php
	exec("export USE_ZEND_ALLOC=0");
	include(dirname(__FILE__) . "/simple_html_dom.php");
	// 喜帖
	vendor_hp("http://verywed.com/classified/list.php?thread=6", "div.list_vendor_box_col a.textcolor2", "classified");
	// 婚顧
	vendor_hp("http://verywed.com/classified/list.php?thread=51", "div.list_vendor_box_col a.textcolor2", "classified");
	// 婚禮音樂
	vendor_hp("http://verywed.com/classified/list.php?thread=36", "div.list_vendor_box_col a.textcolor2", "classified");
	// 婚戒金飾
	vendor_hp("http://verywed.com/classified/list.php?thread=3", "a.bigphototitle,a.phototitle", "classified");
	// 旅遊服務
	vendor_hp("http://verywed.com/classified/list.php?thread=47", "div.list_vendor_box_col a.textcolor2", "classified");
	// 主持人
	vendor_hp("http://verywed.com/classified/list.php?thread=67", "div.makeupContent table a", "classified");
	// 寶寶攝影
	$url = "http://verywed.com/vendor/key.php?key=0225416133";
	echo $url . "\n";
	vendor_hp_nav($url, "div#nav-main a.button", "classified");
	// 佈置
	vendor_hp("http://verywed.com/deco/list-florist.php", "div.makeupContent table a", "deco");
	// 婚禮小物
	vendor_hp("http://verywed.com/deco/list-gift.php", "div.makeupContent table a", "deco");
	// 婚紗攝影
	vendor_hp("http://verywed.com/wedStreet/list.php", "div.makeupContent table a", "wedStreet");
	// 宴客場地
	vendor_hp("http://verywed.com/restaurant/list.php", "div.makeupContent table a", "restaurant");
	// 新娘秘書
	vendor_hp("http://verywed.com/makeup/list.php", "div.makeupContent table a", "makeup");
	// 婚禮紀錄 
	vendor_hp("http://verywed.com/wedRec/list.php", "div.makeupContent table a", "wedRec");
?>
