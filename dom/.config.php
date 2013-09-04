<?php
	Define('XMLROOT', dirname(__FILE__) . '/output/'); // 放 XML 實體檔案的位置
	Define('GZROOT', dirname(__FILE__) . '/output/'); // 放 gzip 實體檔案的位置
 	Define('WWWRoot', "http://verywed.com/event/sitemap/"); // 網址相對位置
	Define('MEMBERPATH_DIARY', dirname(__FILE__) . '/member_diary/');
	Define('MEMBERPATH_FULL', dirname(__FILE__) . '/member_full/');
	Define('VENDORPATH_DIARY', dirname(__FILE__) . '/vendor_diary/');
	Define('VENDORPATH_FULL', dirname(__FILE__) . '/vendor_full/');
	Define('VENDORPATH_HP', dirname(__FILE__) . '/vendor_hp/');
	Define("URLLIMIT", 50000);
	Define("XMLSIZE", 10485760);
	$mem_projects = array("vw", "vwblog", "member", "album", "forum", "evaluate");
	$ven_projects = array("album", "video", "blog", "prom");
?>
