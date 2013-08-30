<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");

	if(isset($argv[1])) {
		if(file_exists(MEMBERPATH . $argv[1])) {
			$rows = array($argv[1]);
		} else {
			exit("資料夾不存在\n");
		}
	} else {
		$rows = array("vw", "vwblog", "member", "album", "forum", "evaluate");
	}

	foreach($rows as $row) {
		sitemapProcess(MEMBERPATH . $row . "/", "rd-member-" . $row . ".xml");
	}

	if(count($rows) > 1) {
 		echo "產生 Sitemap Index: " . ((buildMainXML($rows, "member-index.xml")) ? "成功" : "失敗") . "\n";
	}
?>
