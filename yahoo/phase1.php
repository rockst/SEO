<?php
	include(dirname(__FILE__) . "/.config.php");
	include(dirname(__FILE__) . "/.library.php");
	include(dirname(__FILE__) . "/simple_html_dom.php");

	$page_first = (!empty($argv[1]) && intval($argv[1]) > 0) ? intval($argv[1]) : 1;
	$page_limit = (!empty($argv[2]) && intval($argv[2]) > 0) ? intval($argv[2]) : 3;

	if($argv[3] == "essence") {
		get_url_list("http://verywed.com/forum/essence/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "essence");
  	get_url_list("http://verywed.com/forum/wlessence", "td.subject a[href$=-1.html]", $page_first, $page_limit, "essence");
	}

	if($argv[3] == "expexch") {
  	get_url_list("http://verywed.com/forum/expexch/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
	}

	if($argv[3] == "forum") {
  	get_url_list("http://verywed.com/forum/expexch/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
  	get_url_list("http://verywed.com/forum/wedlife/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
  	get_url_list("http://verywed.com/forum/trade/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
  	get_url_list("http://verywed.com/forum/travelcomp/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
  	get_url_list("http://verywed.com/forum/mtalks/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
  	get_url_list("http://verywed.com/forum/hongkong/list/{page}.html", "td.subject a[href$=-1.html]", $page_first, $page_limit, "forum");
	}

	
	if($argv[3] == "blog") {
		get_url_list("http://verywed.com/vwblog/channel/baby?p={page}", "div#articles div.excerpt h5 a", $page_first, $page_limit, "blog");
		get_url_list("http://verywed.com/vwblog/channel/food?p={page}", "div#articles div.excerpt h5 a", $page_first, $page_limit, "blog");
		get_url_list("http://verywed.com/vwblog/channel/travel?p={page}", "div#articles div.excerpt h5 a", $page_first, $page_limit, "blog");
		get_url_list("http://verywed.com/vwblog/channel/wedding?p={page}", "div#articles div.excerpt h5 a", $page_first, $page_limit, "blog");
		get_url_list("http://verywed.com/vwblog/channel/wedlife?p={page}", "div#articles div.excerpt h5 a", $page_first, $page_limit, "blog");
	}

	if($argv[3] == "ven_blog") {
  	get_url_list("http://verywed.com/classified/blog.php?p={page}", "div#list_box_text a.link-71", $page_first, $page_limit, "ven_blog");
	}
?>
