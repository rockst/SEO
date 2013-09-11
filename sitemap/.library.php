<?php

	// 取得帳號所擁有的試算表文件
	function lstSpreadsheets() {
		GLOBAL $SpreadsheetService;
		$rows = array();
		try {
			$feed = $SpreadsheetService->getSpreadsheetFeed();
			foreach($feed->entries as $entry) {
				if(preg_match("/^" . SpreadsheetName . "/i", $entry->title->text)) {
					$rows[] = array("name"=>$entry->title->text, "key"=>basename($entry->id));
				}
			}
		} catch (Exception $e) {
			echo "Caught exception lstSpreadsheets: " . $e->getMessage() . "\n";
		}	
		return $rows;
	}

	// 取得設定工作表
	function getWorksheet($spreadsheetsKey, $worksheetId) {
		GLOBAL $SpreadsheetService;
		$rows = array();
		try {
			$query = new Zend_Gdata_Spreadsheets_CellQuery();
			$query->setSpreadsheetKey($spreadsheetsKey);
			$query->setWorksheetId($worksheetId);
			$cellFeed = $SpreadsheetService->getCellFeed($query);
			foreach($cellFeed as $cellEntry) {
				$row = $cellEntry->cell->getRow();
				$col = $cellEntry->cell->getColumn();
				$val = $cellEntry->cell->getText();
				if($row == 1) {
					$first[] = $val;
				} else {
					$rows[(intval($row) - 2)][$first[($col - 1)]] = $val;
				}
			}
		} catch (Exception $e) {
			echo "Caught exception getWorksheet: " . $e->getMessage() . "\n";
		}	
		return $rows;
	}

	// 確認是否安裝 PHP Modules
	function chkModules($name) {
		echo "Install " . $name . " PHP Modules?";
		if(extension_loaded($name)) {
			echo "Yes\n";
			return true;
		} else {
			echo "No\n";
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
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
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

	// 建立 XML 檔案
	function buildXML2($filename, &$rows, $spreadsheetsKey, $worksheetId) {
		GLOBAL $SpreadsheetService, $msg;
		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		$num = 0;
		foreach($rows as $i=>$row) {
			$is_incorrectformat = true;
			if(!_chkLoc($row["URL"])) { 
				$is_incorrectformat = false;
				$msg["IncorrectFormat"][] = ($i + 2) . "-B";
			}
			if(!_chkPriority($row["priority"])) { 
				$is_incorrectformat = false;
				$msg["IncorrectFormat"][] = ($i + 2) . "-C";
			}
			if(!_chkChangefreq($row["changefreq"])) { 
				$is_incorrectformat = false;
				$msg["IncorrectFormat"][] = ($i + 2) . "-D";
			}
			if($is_incorrectformat == true) {
				$urls = buildPageURL($row["URL"], ((isset($row["page"])) ? $row["page"] : "")); // 產生多頁 URL
				foreach($urls as $url) {
					$URL = $Sitemap->addChild('url');
					$URL->addChild('loc', $url);
					$URL->addChild('priority', $row["priority"]);
					$URL->addChild('changefreq', $row["changefreq"]);
					$URL->addChild('lastmod', getNow());
					$num++;
				}
			}
		}
		$fp = fopen($source, 'w');
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? $num : 0; // 傳回 XML 檔案是否建立成功
	}
	
	// 建立主要的 sitemap.xml 檔案
	function buildMainXML(&$info) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . MainSitemap;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
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

	// 建立主要的 sitemap.xml 檔案
	function buildMainXML2(&$rows) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . MainSitemap;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		foreach($rows as $row) {
			$url = $Sitemap->addChild('sitemap');
			$url->addChild('loc', WWWRoot . $row["filename"] . ".gz");
			$url->addChild('lastmod', getNow());
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

	// 檢查 URL 格式 http://(m.)yourweb.com
	function _chkLoc(&$loc) {
		$loc = htmlspecialchars(trim($loc));
		if(preg_match("/^http:\/\/(m\.)*" . DOMAIN . "/i", $loc)) {
			return true;
		} else {
			echo "--- loc: " . $loc . " Incorrect Format\n";
			return false;
		}
	}

	// 檢查重要性指標介於 0.0 ~ 1.0
	function _chkPriority(&$priority) {
		$buffer = $priority;
		$priority = sprintf("%01.1f", $priority);	
		if($priority >= 0.1 && $priority <= 1) {
			return true;
		} else {
			echo "--- priority: " . $buffer . " Incorrect Format\n";
			return false;
		}
	}

	// 檢查更新頻率 Const: ChangeFreq in .config.php
	function _chkChangefreq(&$changefreq) {
		$changefreq = trim($changefreq);
		if(preg_match("/" . ChangeFreq . "/i", $changefreq)) {
			return true;
		} else {
			echo "--- changefreq: " . $changefreq . " Incorrect Format\n";
			return false;
		}
	}

	// 檢查最後更新日期格式 YYYY-MM-DD | YYYY/MM/DD	
	function _chkLastmod(&$lastmod) {
		if(preg_match("/^[0-9]{4}(\/|-){1}[0-9]{1,}(\/|-){1}[0-9]{1,}/i", $lastmod)) {
			// 轉為 W3C 格式
			$D = new DateTime(trim($lastmod));	
			$lastmod = $D->format(DateTime::W3C);
			return true;
		} else {
			echo "--- lastmod: " . $lastmod . " Incorrect Format\n";
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
			echo "-- Submit Sitemap to " . $row[0] . ": "; 
			$response = file_get_contents($row[1] . urlencode($xml));
			echo (($response) ? $response : "Failed to submit sitemap") . "\n";
			sleep(1);
		}
	}

	// 透過 curl 的方式提交搜尋引擎
	function SubmitSitemapCurl($xml) {
		GLOBAL $SearchSite; // in .config.php
		foreach($SearchSite as $row) {
			echo "-- Submit Sitemap to " . $row[0] . ": "; 
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
	// 	$url: http://yourweb.com/vwblog/?p=1
	// 	$pattern: p=3
	// 產生
	// 	http://yourweb.com/vwblog/?p=1
	// 	http://yourweb.com/vwblog/?p=2
	// 	http://yourweb.com/vwblog/?p=3
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

	// 檢查日期是否合法
	function _chkDate($date = "") {
		if(!$date || empty($date)) { 
			return false; 
		}
		$tmp = @explode("-", $date);
		if(!checkdate($tmp[1], $tmp[2], $tmp[0])) {
			echo "date dormat is false (YYYY-MM-DD)\n";
			return false;
		}
		return true;
	}

	// 比對開始和結束的日期是否不合法
	function _compDate($date1 = "", $date2 = "") {
		if(!$date1 || empty($date1)) { return false; }
		if(!$date2 || empty($date2)) { return false; }
		$tmp1 = explode("-", $date1);
		$tmp2 = explode("-", $date2);
		if(intval($tmp1[0] . $tmp1[1] . $tmp1[2]) > intval($tmp2[0] . $tmp2[1] . $tmp2[2])) {
			echo "date is false\n";
			return false;
		}
		return true;
	}

	/*
	// 從 Google Analytics 取得資料
	// $ga: Google Analy PHP Insterface Object
	// $totalCount: 總筆數
	// $gaResults: 從 GA 取回資料的變數
	// $ga_id: GA 帳號 ID
	// $dimensions: 維度
	// $metrics: 指標
	// $sort: 排序指標
	// $filter: 篩選資料指標
	// $date1: 開始日期
	// $date2: 結束日期
	// $offset: 從哪一個位置開始抓資料
	// $limit: 一次取得幾筆資料
	*/
	function getGAResults(&$ga, &$totalCount, &$gaResults, $ga_id, $dimensions, $metrics, $sort, $filter, $date1, $date2, $offset, $limit) {
		GLOBAL $counter, $result_cnt;

		try {
			// 產生 request 給 Google Analytics 取得資料
			$ga->requestReportData($ga_id, $dimensions, $metrics, $sort, $filter, $date1, $date2, $offset, $limit);
			// 取得總筆數（避免遞回重覆取得資料，只取得一次）
			if($totalCount == NULL) {
				$totalCount = intval($ga->getTotalResults());
			}
			// 傳送 request 給 Google Analytics 取得資料
			$rows = $ga->getResults();
			if(empty($rows) || count($rows) == 0) {
				return "";
			}
			$result_cnt += count($rows);
			$gaResults = array_merge($gaResults, $rows);
			unset($tmp);
			// echo $offset . "-" . $limit . " count: ".count($gaResults)." - cnt: ".$result_cnt . "\n";
			/*
			// 使用遞回繼續取得資料
			echo ".";
			if($totalCount > 0 && $result_cnt < $totalCount) {
				$counter += 1;
				getGAResults($ga, $totalCount, $gaResults, $ga_id, $dimensions, $metrics, $sort, $filter, $date1, $date2, ($limit * $counter + 1), $limit);
			}
			*/
		} catch(exception $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
		}
	}

	// 取得上一週日期
	function get_befor_week_date() {
		$num = @date("w", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		if($num == 0) { // 因應 GA 週開始是星期日
			$bw = @date("YW", mktime(0, 0, 0, date("m"), date("d") + 1 - 7, date("Y")));
		} else {
			$bw = @date("YW", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
		}
		return getWeekDate(substr($bw, 0, 4), substr($bw, 4, 2));
	}

	// 取得現在是第幾週 
	function get_today_week() {
		$weekNum = @date("w", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		if($weekNum == 0) { // 因應 GA 週開始是星期日
			$tw = @date("YW", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
		} else {
			$tw = @date("YW", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		}
		return $tw;
	}

	// 算出前幾週
	function get_befor_week($year, $weekNum, $inc_week) {
		$date = getWeekDate($year, $weekNum); // 週換日期
		if(empty($date[0]) || empty($date[1])) {
			return 0;
		}
		// 開始日期
		list($y, $m, $d) = explode("-", $date[0]);
		$num = @date("w", mktime(0, 0, 0, $m, $d, $y));
		if($num == 0) { // 因應 GA 週開始是星期日
			$bw = @date("YW", mktime(0, 0, 0, $m, $d + 1 - ($inc_week * 7), $y));
		} else {
			$bw = @date("YW", mktime(0, 0, 0, $m, $d - ($inc_week * 7), $y));
		}
		return $bw;
	}

	// 算出後幾週
	function get_after_week($year, $weekNum, $inc_week) {
		$date = getWeekDate($year, $weekNum); // 週換日期
		if(empty($date[0]) || empty($date[1])) {
			return 0;
		}
		// 開始日期
		list($y, $m, $d) = explode("-", $date[0]);
		$num = @date("w", mktime(0, 0, 0, $m, $d, $y));
		if($num == 0) {
			$iw = @date("YW", mktime(0, 0, 0, $m, $d + 1 + ($inc_week * 7), $y));
		} else {
			$iw = @date("YW", mktime(0, 0, 0, $m, $d + ($inc_week * 7), $y));
		}
		return $iw;
	}

	// 週轉換日期 
	function getWeekDate($year,$weeknum){  
		$firstdayofyear=@mktime(0,0,0,1,1,$year);  
		$firstweekday=@date('N',$firstdayofyear);  
		$firstweenum=@date('W',$firstdayofyear);  
		if($firstweenum==1){  
			$day=(1-($firstweekday-1))+7*($weeknum-1);  
			$startdate=@date('Y-m-d',mktime(0,0,0,1,$day-1,$year));  
			$enddate=@date('Y-m-d',mktime(0,0,0,1,$day+6-1,$year));  
		}else{  
			$day=(9-$firstweekday)+7*($weeknum-1);  
			$startdate=@date('Y-m-d',mktime(0,0,0,1,$day-1,$year));  
			$enddate=@date('Y-m-d',mktime(0,0,0,1,$day+6-1,$year));  
		}  
		return array($startdate,$enddate);      
	}
?>
