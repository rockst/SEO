<?php
	/** 
	* 取得Google Doc 帳號下所擁有的試算表文件
	* 
	* @param Object $SpreadsheetService
	* @return Array $rows
	*/
	function lstSpreadsheets($SpreadsheetName = "") {
		GLOBAL $SpreadsheetService;
		$rows = array();
		try {
			$feed = $SpreadsheetService->getSpreadsheetFeed();
			foreach($feed->entries as $entry) {
				if(preg_match("/^" . $SpreadsheetName . "/i", $entry->title->text)) {
					$rows[] = array("name"=>$entry->title->text, "key"=>basename($entry->id));
				}
			}
		} catch (Exception $e) {
			echo "Caught exception lstSpreadsheets: " . $e->getMessage() . "\n";
		}	
		return $rows;
	}

	/**
	* 取得『設定檔』工作表，通常是左邊數來第一張工作表
	* 用來記錄有哪一些工作表需要被處理以及程式執行的結果
	*
	* @param Object $SpreadsheetService
	* @param String $spreadsheetsKey
	* @param String $worksheetId
	* @return Array $rows
	**/
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

	/**
	* FTP Sitemap 到 beta 機器，僅在開發環境適用
	*
	* @param String $filename（單個檔案）or ""（整個資料夾）
	* @return boolean
	**/
	function ftp2beta($filename = "") {
		$conn_id 		= ftp_connect(FTP_Server); // FTP_Server: .config.php
		$login_result 	= ftp_login($conn_id, FTP_USER, FTP_PAWD); // FTP_USER, FTP_PAWD: .account.php
		if($conn_id && $login_result) { 
			if($filename != "") { // 單個檔案 ftp 到 beta
				if(!ftp_put($conn_id, FTP_Path . $filename, GZROOT . $filename, FTP_BINARY)) { 
					echo "FTP upload has failed!\n";
					return false;
				}
			} else { // 整個資料夾 ftp 到 beta
				ftp_uploaddirectory($conn_id, GZROOT, FTP_Path);
			}
			ftp_quit($conn_id);
			return true;
		} else {
			if(!$conn_id) 		 echo "FTP connection has failed\n";
			if(!$login_result) echo "Attempted to connect to " . FTP_Server . " for user " . FTP_USER . "\n"; 
			return false;
		}
	}

	/**
	* FTP 整個資料夾裡的檔案
	*
	* @param Object $conn_id
	* @param String $local_dir
	* @param String $remote_dir
	* @return none
	**/
	function ftp_uploaddirectory($conn_id, $local_dir, $remote_dir) {
		$f = array();
		@ftp_mkdir($conn_id, $remote_dir);
		$handle = opendir($local_dir);
		while(($file = readdir($handle)) !== false) {
			if(($file != '.') && ($file != '..')) {
				if(is_dir($local_dir . $file)) {
					ftp_uploaddirectory($conn_id, $local_dir . $file . '/', $remote_dir . $file . '/');
				} else {
					$f[] = $file;
				}
			}
		}
		closedir($handle);
		if(count($f)) {
			sort($f);
			ftp_chdir($conn_id, $remote_dir);
			foreach ($f as $files) {
				$from = fopen($local_dir . $files, 'r');
				ftp_fput($conn_id, $files, $from, FTP_BINARY);
			}
		}
	}

	/**
	* 透過 curl 同步 beta 的 Sitemap 資料到線上機器
	*
	* @return Int $code (http request code)
	**/
	function rsync2online() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, Rsync_URL); // Rsync_URL: .config.php
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("host"=>"", "rType"=>"event", "inpDir"=>"sitemap", "dName"=>"sitemap"))); 
		$head = curl_exec($ch); 
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $code;
	}

	/**
	* 確認是否安裝 PHP Modules
	*
	* @param String $name
	* @return boolean
	**/
	function chkModules($name) {
		if(extension_loaded($name)) {
			return true;
		} else {
			echo "Install " . $name . " PHP Modules? No\n";
			return false;
		}
	}

	/**
	* 建立 XML 檔案實體檔案
	*
	* @param String $filename (^[a-zA-Z0-9]+.xml$)
	* @param Array $rows
	* @param Array $msg
	* @return Int $num (threads 數量) or 0 (失敗)
	**/
	function buildXML($filename, &$rows) {
		GLOBAL $msg; // Script file

		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
		fclose($fp);

		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		$num = 0;
		$rows = array_unique_tree($rows); // 瘦身 XML thread 數量
		foreach($rows as $i=>$row) {
			// 驗證傳入的參數，錯誤訊息 $msg 會寫回到 Google Doc 試算表的工作表中（左邊數來第一張工作表）
			$is_incorrectformat = true;
			if(!_chkLoc($row["URL"])) { // URL 格式 
				$is_incorrectformat = false;
				$msg["IncorrectFormat"][] = ($i + 2) . "-B";
			}
			if(!_chkPriority($row["priority"])) { // 權重分數 0.0 ~ 1.0
				$is_incorrectformat = false;
				$msg["IncorrectFormat"][] = ($i + 2) . "-C";
			}
			if(!_chkChangefreq($row["changefreq"])) { // 更新頻率
				$is_incorrectformat = false;
				$msg["IncorrectFormat"][] = ($i + 2) . "-D";
			}

			if($is_incorrectformat == true) { // 傳入的參數皆正確
				// 產生多頁 URL
				$urls = buildPageURL($row["URL"], ((isset($row["page"])) ? $row["page"] : ""));
				// 開始產生 XML threads
				foreach($urls as $url) {
					$URL = $Sitemap->addChild("url");
					$URL->addChild("loc", $url);
					$URL->addChild("priority", $row["priority"]);
					$URL->addChild("changefreq", $row["changefreq"]);
					$URL->addChild("lastmod", getNow());
					$num++;
				}
			}
		}
		$fp = fopen($source, "w");
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? $num : 0; // 傳回 XML 檔案是否建立成功: $num(threads 數量)| 0 (失敗)
	}

	/** 
	* The same thing than implode function, but return the keys so 
	* 
	* <code> 
	* $_GET = array('id' => '4587','with' => 'key'); 
	* ... 
	* echo shared::implode_with_key('&',$_GET,'='); // Resultado: id=4587&with=key 
	* ... 
	* </code> 
	* 
	* @param string $glue Oque colocar entre as chave => valor 
	* @param array $pieces Valores 
	* @param string $hifen Separar chave da array do valor 
	* @return string 
	* @author memandeemail at gmail dot com 
	*/ 
	function implode_with_key($glue = null, $pieces, $hifen = ',') { 
		$return = null; 
		foreach ($pieces as $tk => $tv) $return .= $glue.$tk.$hifen.$tv; 
		return substr($return,1); 
	}
	/** 
	* Return unique values from a tree of values 
	* 
	* @param array $array_tree 
	* @return array 
	* @author memandeemail at gmail dot com 
	*/
	function array_unique_tree($array_tree) { 
		$will_return = array(); $vtemp = array(); 
		foreach ($array_tree as $tkey => $tvalue) $vtemp[$tkey] = implode_with_key('&',$tvalue,'='); 
		foreach (array_keys(array_unique($vtemp)) as $tvalue) $will_return[$tvalue] = $array_tree[$tvalue]; 
		return $will_return; 
	}

	/**
	* 建立主要索引 Sitemap 用的 XML 檔案
	*
	* @param Array $rows
	* @return boolean
	**/
	function buildMainXML(&$rows) {
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

	/**
	* 建立 gzip 檔案 
	*
	* @param String $filename (^[a-zA-Z0-9]+.xml$)
	* @return boolean
	**/
	function buildGZ($filename) {
		$source = GZROOT . $filename . ".gz";
		$fp = gzopen($source, "w9"); // w9 = highest compression
		gzwrite ($fp, file_get_contents(XMLROOT . $filename));
		gzclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 gzip 檔案是否建立成功
	}

	/**
	* 檢查 XML 檔案名稱格式
	*
	* @param String $xml (^[a-zA-Z0-9]+.xml$)
	* @return boolean
	**/
	function _chkXML(&$xml) {
		$xml = trim($xml);
		return (preg_match("/[a-zA-Z0-9]+.xml$/i", $xml)) ? true : false;
	}

	/**
	* 檢查 URL 格式
	*
	* @param String $loc (^http://(m.)verywed.com$)
	* @return boolean
	**/
	function _chkLoc(&$loc) {
		$loc = htmlspecialchars(trim($loc));
		if(preg_match("/^http:\/\/(m\.)*" . DOMAIN . "/i", $loc)) { // DOMAIN: .config.php
			return true;
		} else {
			echo "--- loc: " . $loc . " Incorrect Format\n";
			return false;
		}
	}

	/**
	* 檢查重要性指標介於 0.0 ~ 1.0
	*
	* @param Float $priority
	* @return boolean
	**/
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

	/**
	* 檢查更新頻率
	*
	* @param String $changefreq
	* @return boolean
	**/
	function _chkChangefreq(&$changefreq) {
		$changefreq = trim($changefreq);
		if(preg_match("/" . ChangeFreq . "/i", $changefreq)) { // ChangeFreq: .config.php
			return true;
		} else {
			echo "--- changefreq: " . $changefreq . " Incorrect Format\n";
			return false;
		}
	}

	/**
	* 檢查最後更新日期格式
	*
	* @param String $lastmod (YYYY-MM-DD | YYYY/MM/DD)
	* @return boolean
	**/
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
	
	/**
	* 取得現在的日期時間
	*
	* @return W3C Datetime
	**/
	function getNow() {
		$D = new DateTime("NOW");
		return $D->format(DateTime::W3C);
	}

	/**
	* 透過 file_get_contents 的方式提交搜尋引擎
	*
	* @param String $xml（XML 檔案的 URL）
	* @return none
	**/
	function SubmitSitemapFGC($xml) {
		GLOBAL $SearchSite; // in .config.php
		foreach($SearchSite as $row) {
			echo "-- Submit Sitemap to " . $row[0] . ": "; 
			$response = file_get_contents($row[1] . urlencode($xml));
			echo (($response) ? $response : "Failed to submit sitemap") . "\n";
			sleep(1);
		}
	}

	/**
	* 透過 curl 的方式提交搜尋引擎
	*
	* @param String $xml（XML 檔案的 URL）
	* @return none
	**/
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

	/**
	* 比對 pattern 與網址 $url 的相關性，爾後產生多筆分頁網址
	* 說明：
	* 	$url: http://yourweb.com/vwblog/?p=1
	* 	$pattern: p=3
	* 產生：
	* 	http://yourweb.com/vwblog/?p=1
	* 	http://yourweb.com/vwblog/?p=2
	* 	http://yourweb.com/vwblog/?p=3
	*
	* @param String $url
	* @param String $pattern or ""
	* @return Array $urls
	**/
	function buildPageURL($url, $pattern = "") {
		$urls = array();
		// 產生分頁的 URL
		if($pattern != "" && preg_match_all("/[0-9]{1,}/i", $pattern, $match, PREG_PATTERN_ORDER) && isset($match[0][0]) && intval($match[0][0]) > 0) { // PREG_PATTERN_ORDER: .config.php
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

	/**
	* 檢查日期是否合法
	*
	* @param String $date (YYYY-MM-DD) or ""
	* @return boolean
	**/
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

	/**
	* 比對開始和結束的日期是否不合法
	*
	* @param String $date1 or ""
	* @param String $date2 or ""
	* @return boolean
	**/
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

	/**
	* 從 Google Analytics 取得資料
	*
	* @param Object $ga: Google Analy PHP Insterface Object
	* @param Int $totalCount: 總筆數
	* @param Array $gaResults: 從 GA 取回資料的變數
	* @param Int $ga_id: GA 帳號 ID
	* @param String $dimensions: 維度
	* @param String $metrics: 指標
	* @param String $sort: 排序指標
	* @param String $filter: 篩選資料指標
	* @param String $date1: 開始日期
	* @param String $date2: 結束日期
	* @param Int $offset: 從哪一個位置開始抓資料
	* @param Int $limit: 一次取得幾筆資料
	* @return $gaResults（傳址變數）	
	**/
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
		} catch(exception $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
		}
	}

	/**
	* 取得上一週日期
	*
	* @return String
	**/
	function get_befor_week_date() {
		$num = @date("w", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		if($num == 0) { // 因應 GA 週開始是星期日
			$bw = @date("YW", mktime(0, 0, 0, date("m"), date("d") + 1 - 7, date("Y")));
		} else {
			$bw = @date("YW", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
		}
		return getWeekDate(substr($bw, 0, 4), substr($bw, 4, 2));
	}

	/**
	* 取得現在是第幾週 
	*
	* @return String $tw
	**/
	function get_today_week() {
		$weekNum = @date("w", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		if($weekNum == 0) { // 因應 GA 週開始是星期日
			$tw = @date("YW", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
		} else {
			$tw = @date("YW", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		}
		return $tw;
	}

	/**
	* 算出前幾週
	*
	* @param Int $year
	* @param Int $weekNum
	* @param Int $inc_week
	* @return Int $bw
	**/
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

	/**
	* 算出後幾週
	*
	* @param Int $year
	* @param Int $weekNum
	* @param Int $inc_week
	* @return Int $iw
	**/
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

	/**
	* 週轉換日期 
	*
	* @param Int $year
	* @param Int $weeknum
	* @return Array
	**/
	function getWeekDate($year, $weeknum) {
		$firstdayofyear = @mktime(0, 0, 0, 1, 1, $year);
		$firstweekday = @date('N', $firstdayofyear);
		$firstweenum = @date('W', $firstdayofyear);
		if($firstweenum == 1) {
			$day = (1 - ($firstweekday - 1)) + 7 * ($weeknum - 1);
			$startdate = @date('Y-m-d', mktime(0, 0, 0, 1, $day - 1, $year));
			$enddate = @date('Y-m-d', mktime(0, 0, 0, 1, $day + 6 - 1, $year));
		} else {
			$day = (9 - $firstweekday) + 7 * ($weeknum - 1);
			$startdate = @date('Y-m-d', mktime(0, 0, 0, 1, $day - 1, $year));
			$enddate = @date('Y-m-d', mktime(0, 0, 0, 1, $day + 6 - 1, $year));
		}
		return array($startdate, $enddate);
	}
?>
