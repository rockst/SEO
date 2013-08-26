<?php

	// 確認是否安裝 PHP Modules
	function chkModules($name) {
		echo "確認是否安裝 " . $name . " PHP Modules？";
		if(extension_loaded($name)) {
			echo "有\n";
			return true;
		} else {
			echo "尚未\n";
			return false;
		}
	}

	// 取得帳號下所有的試算表
	function getWorkFeedNumbs(&$spreadsheetService, &$query) {
		$query->setWorksheetId(MAINWORKID);
		$feed = $spreadsheetService->getListFeed($query);
		$info = array();
		foreach($feed->entries as $row) {
			foreach($row->getCustom() as $Custom) {
				if($Custom->getText()) {
					array_push($info, array($Custom->rootElement, $Custom->getText()));
				}
			}
		}
		return $info;
	}

	// 取得工作表下所有資料
	function getWorksheetData($feed, &$rows) {
		foreach($feed->entries as $row) {
			$column = array();
			foreach($row->getCustom() as $row2) {
				array_push($column, $row2->getText());
			}
			array_push($rows, $column);
		}
	}

	// 建立 XML 檔案
	function buildXML($filename, &$rows) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		$i = 0;
		foreach($rows as $row) {
			if(_chkLoc($row[1]) && _chkPriority($row[2]) && _chkChangefreq($row[3]) && _chkLastmod($row[4])) { // 驗證
				// 產生多頁 URL
				$urls = buildPageURL($row[1], $row[5]);
				foreach($urls as $url) {
					$URL = $Sitemap->addChild('url');
					$URL->addChild('loc', $url);
					$URL->addChild('priority', $row[2]);
					$URL->addChild('changefreq', $row[3]);
					$URL->addChild('lastmod', $row[4]);
					$i++;
				}
			}
		}
		$fp = fopen($source, 'w');
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? $i : 0; // 傳回 XML 檔案是否建立成功
	}
	
	// 建立主要的 sitemap.xml 檔案
	function buildMainXML(&$info) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . MainSitemap;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		for($i = 0; $i < count($info); $i++) {
			if($info[$i][2] > 0) {
				$url = $Sitemap->addChild('sitemap');
				$url->addChild('loc', WWWRoot . $info[$i][1] . ".gz");
				$url->addChild('lastmod', getNow());
			}
		}
		$fp = fopen($source, 'w');
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 XML 檔案是否建立成功
	}

	// 建立 gzip 檔案 
	function buildGZ($filename) {
		$source = GZROOT . $filename . '.gz';
		$fp = gzopen($source, 'w9'); // w9 = highest compression
		gzwrite ($fp, file_get_contents(XMLROOT . $filename));
		gzclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 gzip 檔案是否建立成功
	}

	// 檢查 XML 檔案名稱格式 [a-zA-Z0-9]+.xml
	function _chkXML(&$xml) {
		$xml = trim($xml);
		return (preg_match("/[a-zA-Z0-9]+.xml$/i", $xml)) ? true : false;
	}

	// 檢查 URL 格式 http://(m.)verywed.com
	function _chkLoc(&$loc) {
		$loc = htmlspecialchars(trim($loc));
		if(preg_match("/^http:\/\/(m\.)*" . DOMAIN . "/i", $loc)) {
			return true;
		} else {
			echo "--- loc: " . $loc . " 格式不正確\n";
			return false;
		}
	}

	// 檢查重要性指標介於 0.0 ~ 1.0
	function _chkPriority(&$priority) {
		$priority = sprintf("%01.1f", $priority);	
		if($priority >= 0 && $priority <= 1) {
			return true;
		} else {
			echo "--- priority: " . $priority . " 格式不正確\n";
			return false;
		}
	}

	// 檢查更新頻率 Const: ChangeFreq in .config.php
	function _chkChangefreq(&$changefreq, $isMsg = true) {
		$changefreq = trim($changefreq);
		if(preg_match("/" . ChangeFreq . "/i", $changefreq)) {
			return true;
		} else {
			if($isMsg) echo "--- changefreq: " . $changefreq . " 格式不正確\n";
			return false;
		}
	}

	// 檢查最後更新日期格式 YYYY-MM-DD | YYYY/MM/DD	
	function _chkLastmod(&$lastmod, $isMsg = true) {
		if(preg_match("/^[0-9]{4}(\/|-){1}[0-9]{1,}(\/|-){1}[0-9]{1,}/i", $lastmod)) {
			// 轉為 W3C 格式
			$D = new DateTime(trim($lastmod));	
			$lastmod = $D->format(DateTime::W3C);
			return true;
		} else {
			if($isMsg) echo "--- lastmod: " . $lastmod . " 格式不正確\n";
			return false;
		}
	}

	// 取得現在的日期時間
	function getNow() {
		$D = new DateTime('NOW');
		return $D->format(DateTime::W3C);
	}

	// 透過 file_get_contents 的方式提交搜尋引擎
	function SubmitSitemapFGC($xml) {
		GLOBAL $SearchSite; // in .config.php
		foreach($SearchSite as $row) {
			echo "-- 開始提交 " . $row[0] . "："; 
			$response = file_get_contents($row[1] . urlencode($xml));
			echo (($response) ? $response : "Failed to submit sitemap") . "\n";
			sleep(1);
		}
	}

	// 透過 curl 的方式提交搜尋引擎
	function SubmitSitemapCurl($xml) {
		GLOBAL $SearchSite; // in .config.php
		foreach($SearchSite as $row) {
			echo "-- 開始提交 " . $row[0] . "："; 
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $row[1] . urlencode($xml)); 
			curl_setopt($ch, CURLOPT_HEADER, TRUE); 
			curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$head = curl_exec($ch); 
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
			curl_close($ch); 
			echo (($httpCode == 200) ? "Success" : "Failed to submit sitemap") . "\n";
			sleep(1);
		}
	}

	/*
	// 比對 pattern 與網址 $url 的相關性，爾後產生多筆分頁網址
	// 舉例說明：
	// 	$url: http://verywed.com/vwblog/?p=1
	// 	$pattern: p=3
	// 產生
	// 	http://verywed.com/vwblog/?p=1
	// 	http://verywed.com/vwblog/?p=2
	// 	http://verywed.com/vwblog/?p=3
	*/
	function buildPageURL($url, $pattern = "") {

		$urls = array();

		// 產生分頁的 URL
		if($pattern != "" && preg_match_all("/[0-9]{1,}/i", $pattern, $match, PREG_PATTERN_ORDER) && isset($match[0][0]) && intval($match[0][0]) > 0) {
			$limit = $match[0][0];
			$p1 = "(" . substr($pattern, 0, strpos($pattern, $limit)) . ")";
			$p2 = "(" . substr($pattern, (strpos($pattern, $limit) + strlen($limit)), strlen($pattern)) . ")";
			for($i = 1; $i <= intval($limit); $i++) { // 產生多頁
				array_push($urls, preg_replace("/\{R\}/", "", preg_replace("/" . $p1 . "[0-9]{1,}" . $p2 . "/", "\$1{R}$i{R}\$2", $url)));
			}
		} else {
			array_push($urls, $url);
		}

		return $urls;
	}

?>