<?php
	// Google Doc 試算表文件資料
	$GSheetIDs = array(
		"0AhQQp1u38LcBdEhKVDJUNWIzcFNxTndjcGhFWFlpVEE", // 試算表 key
		"0AhQQp1u38LcBdHFPNTlJSVotY1pQcHZIWnp3QjhreFE"
	);
	// 搜尋引擎
	$SearchSite = array(
		array('google', 'http://www.google.com/webmasters/sitemaps/ping?sitemap='),
		array('bing(webmaster)', 'http://www.bing.com/webmaster/ping.aspx?sitemap='),
		array('bing', 'http://www.bing.com/ping?sitemap=')
	);
	// 定義常數
	Define('DOMAIN', 'verywed.com'); // Your domain
	Define('MAINWORKID', "1"); // 預設『第一個工作表』為設定檔
	Define('MainSitemap', "sitemap.xml"); // 主要 sitemap 的檔名
	Define('WWWRoot', "http://verywed.com/event/sitemap/"); // 主要 Sitemap 網址相對位置
	Define('ROBOTSSITEMAP', "http://verywed.com/sitemap.xml"); // Robots 指引的 Sitemap 網址相對位置 
	Define('ChangeFreq', "(always|hourly|daily|weekly|monthly|yearly|never)"); // 更新頻率列表
	Define('FILTERWORKNAME', '/^rd/i'); // 設定檔中的 XML 檔案出現過濾字就不處理
	Define('XMLROOT', dirname(__FILE__) . '/output/'); // 放 XML 實體檔案的位置
	Define('GZROOT', dirname(__FILE__) . '/output/'); // 放 gzip 實體檔案的位置
  Define("SpreadsheetName", "Sitemap - Test"); // 透過試算表命名規則來取得哪幾個試算表文件需要被處理
	// Google Analytics Profile 
	$GA_Profile = Array(
		"wed" =>Array("id"=>5061547, "name"=>"婚前"), // 婚前
		"life"=>Array("id"=>61670331, "name"=>"婚後"), // 婚後 
	);
	// Google Analytics Set dimensions and metrics
	$GA_input = Array(
		// 熱門網頁
		Array(
			"profile"=>array($GA_Profile["wed"], $GA_Profile["life"]),
			"subject" => "熱門網頁",
			"dimensions" => array("pagePath"),
			"metrics" => array("pageviews"),
			"sort" => array("-pageviews"),
			"filter" => ""
		),
		// 熱門結婚經驗交流的討論串網頁
		Array(
			"profile"=>array($GA_Profile["wed"]),
			"subject" => "熱門結婚經驗交流的討論串網頁",
			"dimensions" => array("pagePath"),
			"metrics" => array("pageviews"),
			"sort" => array("-pageviews"),
			"filter" => "ga:pagePath =~ ^/forum/expexch/[0-9]+-1.html$"
		),
		// 熱門婚後生活的討論串網頁
		Array(
			"profile"=>array($GA_Profile["life"]),
			"subject" => "熱門婚後生活的討論串網頁",
			"dimensions" => array("pagePath"),
			"metrics" => array("pageviews"),
			"sort" => array("-pageviews"),
			"filter" => "ga:pagePath =~ ^/forum/wedlife/[0-9]+-1.html$"
		),
		// 熱門廠商首頁
		Array(
			"profile"=>array($GA_Profile["wed"]),
			"subject" => "熱門廠商首頁",
			"dimensions" => array("pagePath"),
			"metrics" => array("pageviews"),
			"sort" => array("-pageviews"),
			"filter" => "ga:pagePath =~ ^/[0-9]+/(makeup|wedRec|deco|wedStreet|restaurant|classified)+$"
		),
		// 熱門從Facebook來的推薦連結
		Array(
			"profile"=>array($GA_Profile["wed"], $GA_Profile["life"]),
			"subject" => "熱門從Facebook來的推薦連結",
			"dimensions" => array("landingPagePath"),
			"metrics" => array("pageviews"),
			"sort" => array("-pageviews"),
			"filter" => "ga:source == facebook"
		),
	);
	// 一次從 GA 取出幾筆
	define('LIMIT', 12500);
?>
