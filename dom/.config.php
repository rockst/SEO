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
   Define("FTP_Server", "beta.home.veryhuman.com"); // Beta FTP Server
   Define("FTP_Path", "/event/sitemap/"); // Beta FTP Path
   Define("Rsync_URL", "http://vipupload.verywed.com/rsync.php"); // Rsync Script URL
	$mem_projects = array("vw", "vwblog", "member", "album", "forum", "evaluate");
	$ven_projects = array("album", "video", "blog", "prom");
	// 搜尋引擎
   $SearchSite = array(
      array('google', 'http://www.google.com/webmasters/sitemaps/ping?sitemap='),
      array('bing(webmaster)', 'http://www.bing.com/webmaster/ping.aspx?sitemap='),
      array('bing', 'http://www.bing.com/ping?sitemap=')
   );
?>
