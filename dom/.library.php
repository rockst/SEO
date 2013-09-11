<?php
	// 處理
	function runScript($index_name, $projects, $script_name, $path_diary, $path_full) {
		GLOBAL $argv;
		// 設定是否要日常還是抓取全部
		if(!isset($argv[1]) || !preg_match("/^(diary|full)+$/i", $argv[1])) {
			return "請輸入是否要日常還是抓取全部：\n- Example：php " . $script_name . ".php diary|full";
		}
		$SCRIPTPATH = ($argv[1] == "diary") ? $path_diary : $path_full;
		// 可以選擇單一專案或全部
		if(isset($argv[2])) { // 單一專案
			if(file_exists($SCRIPTPATH . $argv[2])) {
				$rows = array($argv[2]);
			} else {
				return "資料夾不存在";
			}
		} else { // 全部
			$rows = $projects; 
		}
		// 產生 XML, gzip 檔案
		foreach($rows as $row) {
			sitemapProcess($SCRIPTPATH . $row . "/", $script_name . "-" . $row . ".xml", (($argv[1] == "diary") ? true : false));
		}
		// 更新 Sitemap 索引檔
 		return "產生 Sitemap Index: " . ((buildMainXML($projects, $index_name)) ? "成功" : "失敗");
	}

	// 執行爬網址的主程式
	function sitemapProcess($path, $xml, $isHeap = false) {
		$start = getMicrotime();
   	$rows = array();
		$handle = opendir($path);
		while (false !== ($entry = readdir($handle))) {
			if(preg_match("/.php$/i", $entry) && !preg_match("/^simple_html_dom.php$/i", $entry)) {
				execPHP($rows, $path, $entry);
				if(count($rows) <= URLLIMIT) {
					if(buildXML($xml, $rows, $isHeap)) {
						if(buildGZ($xml)) {
							if(filesize(GZROOT . $xml . ".gz") <= XMLSIZE) { 
								$msg = "處理成功：總筆數 " . count($rows) . " 筆"; 
							} else { $msg = "單一XML的檔案大小不能超過 " . XMLSIZE; }
						} else { $msg = "建立 Gzip 檔案失敗"; }
					} else { $msg = "建立 XML 檔案失敗"; }		
				} else { $msg = "單一XML的筆數不能超過 " . URLLIMIT; }
			}
		}
		echo "===================================================\n";
		echo "訊息：" . $msg . "\n";
		echo "總執行時間：" . (getMicrotime() - $start) . "\n";
		echo "===================================================\n";
		closedir($handle);
	}

	// 執行 PHP 檔案取得 URL
	function execPHP(&$rows, $path, $name) {
		$s = getMicrotime();
		echo "處理 " . $name . " 中：";
		$file = $path . $name;
		if(file_exists($file)) {
			exec(escapeshellcmd("/usr/bin/php " . $file), $output);
			echo "共 " . count($output) . " 筆\n- 執行時間: " . (getMicrotime() - $s) . "\n";
			$rows = array_merge($rows, $output);
		} else {
			echo "檔案不存在\n";
		}
	}

	// 用來計算執行時間
	function getMicrotime() { 
		list($usec, $sec) = explode(' ', microtime()); 
		return ((double)$usec + (double)$sec); 
	}

	// 建立 XML 檔案
	function buildXML($filename, &$rows, $isHeap = false) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename;
		if($isHeap == false) {
			$fp = fopen($source, 'w');
			fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
			fclose($fp);
		}
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		$i = 0;
		foreach($rows as $url) {
			$URL = $Sitemap->addChild('url');
			$URL->addChild('loc', htmlspecialchars($url));
			$URL->addChild('priority', 0.4);
			$URL->addChild('changefreq', "never");
			$URL->addChild('lastmod', getNow());
			$i++;
		}
		$fp = fopen($source, (($isHeap) ? "w+" : "w"));
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		//exec('sed "s/<\/url>/<\/url>\n/g;" ' . $source . '>' . $source);
		return (file_exists($source)) ? $i : 0; // 傳回 XML 檔案是否建立成功
	}

	// 建立 gzip 檔案 
	function buildGZ($filename) {
		$source = GZROOT . $filename . '.gz';
		$fp = gzopen($source, 'w9'); // w9 = highest compression
		gzwrite ($fp, file_get_contents(XMLROOT . $filename));
		gzclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 gzip 檔案是否建立成功
	}

	function getNow() {
		$D = new DateTime('NOW');
		return $D->format(DateTime::W3C);
	}

	// 建立主要的 sitemap.xml 檔案
	function buildMainXML(&$rows, $sitemap) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $sitemap;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		foreach($rows as $row) {
			$url = $Sitemap->addChild('sitemap');
			$url->addChild('loc', WWWRoot . "rd-member-" . $row . ".xml.gz");
			$url->addChild('lastmod', getNow());
		}
		$fp = fopen($source, 'w');
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 XML 檔案是否建立成功
	}

?>
