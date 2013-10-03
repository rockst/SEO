<?php
	Define("XMLROOT", dirname(__FILE__) . "/output/"); // 放 XML 實體檔案的位置
	Define("GZROOT", dirname(__FILE__) . "/output/"); // 放 gzip 實體檔案的位置
 	Define("WWWRoot", "http://verywed.com/event/sitemap/"); // 網址相對位置
	Define("MEMBERPATH_DIARY", dirname(__FILE__) . "/member_diary/"); // 會員日常 UGC 程式
	Define("MEMBERPATH_FULL", dirname(__FILE__) . "/member_full/"); // 會員全部一次匯入程式
	Define("VENDORPATH_DIARY", dirname(__FILE__) . "/vendor_diary/"); // 廠商日常 UGC 程式
	Define("VENDORPATH_FULL", dirname(__FILE__) . "/vendor_full/"); // 廠商全部一次匯入程式
	Define("VENDORPATH_HP", dirname(__FILE__) . "/vendor_hp/"); // 廠商首頁一次匯入程式
	Define("URLLIMIT", 50000); // threads 最大限制
	Define("XMLSIZE", 10485760); // 實體檔案最大限制
   Define("FTP_Server", "beta.home.veryhuman.com"); // Beta FTP Server
   Define("FTP_Path", "/event/sitemap/"); // Beta FTP Path
   Define("Rsync_URL", "http://vipupload.verywed.com/rsync.php"); // Rsync Script URL
	// 會員工具列表
	$mem_projects = array("vw", "vwblog", "member", "album", "forum", "evaluate");
	// 廠商工具列表
	$ven_projects = array("album", "video", "blog", "prom");
	// 搜尋引擎
   $SearchSite = array(
      array("google", "http://www.google.com/webmasters/sitemaps/ping?sitemap="),
      array("bing(webmaster)", "http://www.bing.com/webmaster/ping.aspx?sitemap="),
      array("bing", "http://www.bing.com/ping?sitemap=")
   );
?>
