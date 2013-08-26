<?php
	// 開始爬網址
	function sitemapProcess($path, $xml) {
		$start = getMicrotime();
   	$rows = array();
		$handle = opendir($path);
		while (false !== ($entry = readdir($handle))) {
			if(preg_match("/.php$/i", $entry) && !preg_match("/^simple_html_dom.php$/i", $entry)) {
				execPHP($rows, $path, $entry);
				if(count($rows) <= URLLIMIT) {
					if(buildXML($xml, $rows)) {
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
	function buildXML($filename, &$rows) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
		fclose($fp);
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
		$fp = fopen($source, 'w');
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
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
	
	// 取得現在日期
	function getNow() {
		$D = new DateTime('NOW');
		return $D->format(DateTime::W3C);
	}
?>
