<?php
	/**
	* 執行從網頁取得網址產生 Sitemap 的程式
	*
	* @param String $index_name：Sitemap 索引檔名稱，如 [a-zA-Z0-9]+.xml
	* @param Array $projects：工具資料陣列，用來執行全部一次產生 Sitemap。
	* @param String $script_name：每一個工具的 XML 前置檔名，如 "rd-member"-forum.xml.gz 中的 rd-member
	* @param String $path_diary：執行日常程式路徑
	* @param String $path_full：執行全部程式路徑
	* @param Array $argv：執行 command line 所需要的參數，*.php (diary|full)+ (tool_name)*
	* @return none 
	**/
	function runScript($index_name, $projects, $script_name, $path_diary, $path_full) {
		GLOBAL $argv;

		// 傳入 參數1 是否要日常(diary)還是抓取全部(full)
		if(!isset($argv[1]) || !preg_match("/^(diary|full)+$/i", $argv[1])) {
			return "請輸入是否要抓取日常還是一次匯入全部：\n- Example：php " . $script_name . ".php diary|full";
		}
		$SCRIPTPATH = ($argv[1] == "diary") ? $path_diary : $path_full;

		// 傳入 參數2 可以選擇單一專案(forum, album ... etc)或全部(full)
		if(isset($argv[2])) { // 單一專案
			if(file_exists($SCRIPTPATH . $argv[2])) {
				$rows = array($argv[2]);
			} else {
				exit("資料夾中的工具專案不存在\n");
			}
		} else { // 全部
			$rows = $projects; 
		}

		// 產生 XML 與 gzip 檔案
		foreach($rows as $row) {
			domProcess($SCRIPTPATH . $row . "/", $script_name . "-" . $row . ".xml", (($argv[1] == "diary") ? true : false));
		}

		// 更新 Sitemap 索引檔
 		echo "產生 Sitemap 索引檔: " . ((buildMainXML($projects, $index_name, $script_name)) ? "成功" : "失敗") . "\n";
		echo "FTP to beta: " . ((ftp2beta()) ? "success" : "fail") . "\n";
		echo "Rsync to online: " . ((rsync2online()) ? "success" : "fail") . "\n";
		echo "Submit " . WWWRoot . $index_name . "\n";
		SubmitSitemapCurl(WWWRoot . $index_name);
	}

	/**
	* 執行從網頁中取得網址的主程式，最後產生 sitemap 檔案
	*
	* @param String $path：工具程式的所在位置 
	* @param String $xml：產生 sitemap 檔案的前置檔名
	* @param boolean $isHeap：是否要累加 sitemap 中的 thread，通常是用來執行日常程式使用
	* @return none 
	**/
	function domProcess($path, $xml, $isHeap = false) {

		$start = getMicrotime(); // 開始時間

		// 檢查是否存在 gzip 壓縮檔，若沒有則從線上下載使用
		if(!file_exists(GZROOT . $xml . ".gz")) {
			try {
				file_put_contents(GZROOT . $xml . ".gz", file_get_contents(WWWRoot . $xml . ".gz"));
			} catch (Exception $e) { // 發生錯誤
				exit("Caught exception file_put_contents: " . $e->getMessage() . "\n");
			}
		}
		// 開始執行工具程式
   	$rows = array(); // 放 threads 的資料陣列
		$handle = opendir($path);
		while(($entry = readdir($handle)) !== false) {
			if(preg_match("/.php$/i", $entry) && !preg_match("/^simple_html_dom.php$/i", $entry)) {
				if(!execPHP($rows, $path, $entry)) { $message = "執行" . $entry . "發生錯誤"; } 
				else if(count($rows) > URLLIMIT) { $message = "XML 的 threads 不能超過 " . URLLIMIT; }
				else if(!unGZ($xml)) { $message = "解壓縮檔案失敗"; }
				else if(!buildXML($xml, $rows, $isHeap)) { $message = "建立 XML 檔案失敗"; }
				else if(!slimming($xml)) { $message = "瘦身 XML 檔案失敗"; }
				else if(!buildGZ($xml)) { $message = "建立 Gzip 檔案失敗"; }
				else if(!@unlink(XMLROOT . $xml)) { $message = "刪除 XML 檔案失敗"; }
				else {
					$message = (@filesize(GZROOT . $xml . ".gz") <= XMLSIZE) ? 
									"處理成功：總筆數 " . count($rows) . " 筆" : 
									"單一XML的檔案大小不能超過 " . XMLSIZE;
				}
			}
		}
		closedir($handle);
		echo "===================================================\n";
		echo "訊息：" . $message . "\n";
		echo "總執行時間：" . (getMicrotime() - $start) . "\n";
		echo "===================================================\n";
	}

	/**
   * FTP Sitemap 到 beta 機器
	*
	* @param String $filename：檔案名稱或整個資料夾
	* @return boolean 
	**/
   function ftp2beta($filename = "") {
      $conn_id = @ftp_connect(FTP_Server); // .config.php
      $login_result = @ftp_login($conn_id, FTP_USER, FTP_PAWD); // .account.php
      if($conn_id && $login_result) {
         if($filename != "") { // 單個檔案
            if(!@ftp_put($conn_id, FTP_Path . $filename, GZROOT . $filename, FTP_BINARY)) {
               echo "FTP upload has failed!\n";
               return false;
            }
         } else { // 整個資料夾
            ftp_uploaddirectory($conn_id, GZROOT, FTP_Path);
         }
         @ftp_quit($conn_id);
         return true;
      } else {
         if(!$conn_id)      echo "FTP connection has failed!\n";
         if(!$login_result) echo "Attempted to connect to " . FTP_Server . "for user " . FTP_USER . "\n";
         return false;
      }
   }

	/**
   * FTP 整個資料夾裡的檔案
	*
	* @param Object $conn_id：FTP connecter
	* @param String $local_dir：本地資料夾
	* @param String $remote_dir：目地資料夾
	* @return none
	**/
   function ftp_uploaddirectory($conn_id, $local_dir, $remote_dir) {
      @ftp_mkdir($conn_id, $remote_dir);
      $handle = @opendir($local_dir);
      while(($file = @readdir($handle)) !== false) {
         if(($file != '.') && ($file != '..')) {
            if(is_dir($local_dir . $file)) {
               ftp_uploaddirectory($conn_id, $local_dir . $file . '/', $remote_dir . $file . '/');
            } else {
               $f[] = $file;
            }
         }
      }
      @closedir($handle);
      if(count($f)) {
         sort($f);
         @ftp_chdir($conn_id, $remote_dir);
         foreach ($f as $files) {
            $from = @fopen($local_dir . $files, 'r');
            @ftp_fput($conn_id, $files, $from, FTP_BINARY);
         }
      }
   }

	/**
   * 透過 Curl 同步 beta 資料到線上
	*
	* @param Const Rsync_URL：同步程式網址
	* @return Int $code：HTTP reauest Code 
	**/
   function rsync2online() {
      $ch = @curl_init();
      @curl_setopt($ch, CURLOPT_URL, Rsync_URL); // .config.php
      @curl_setopt($ch, CURLOPT_HEADER, TRUE);
      @curl_setopt($ch, CURLOPT_NOBODY, TRUE);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      @curl_setopt($ch, CURLOPT_POST, TRUE);
      @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("host"=>"", "rType"=>"event", "inpDir"=>"sitemap", "dName"=>"sitemap")));
      $head = @curl_exec($ch);
      $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
      @curl_close($ch);
      return $code;
   }

	/**
	* 執行 PHP 檔案取得 URL
	*
	* @param Array &$rows：抓取到網址的資料陣列 
	* @param String $path：工具程式位置
	* @param String $name：工具程式名稱
	* @return boolean 
	**/
	function execPHP(&$rows, $path, $name) {
		$s = getMicrotime();
		echo "處理 " . $name . " 中：";
		$file = $path . $name;
		if(file_exists($file)) {
			exec(escapeshellcmd("/usr/bin/php " . $file), $output);
			echo "共 " . count($output) . " 筆\n- 執行時間: " . (getMicrotime() - $s) . "\n";
			$rows = array_merge($rows, $output);
			return true;
		} else {
			echo "檔案不存在\n";
			return false;
		}
	}

	/**
	* 用來計算執行時間
	*
	* @param none 
	* @return Int 總秒數
	**/
	function getMicrotime() { 
		list($usec, $sec) = explode(' ', microtime()); 
		return ((double)$usec + (double)$sec); 
	}

	/**
	* 建立 XML 檔案
	*
	* @param String $filename：XML 檔案名稱
	* @param Array &$rows：用來產生 threads 的資料陣列
	* @param Boolean $isHeap：是否用來疊加 XML 的變數，用來使用在日常產生資料中
	* @return Int threads 
	**/
	function buildXML($filename, &$rows, $isHeap = false) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename;
		if($isHeap == false) {
			$fp = fopen($source, "w");
			fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
			fclose($fp);
		}
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		foreach($rows as $url) {
			$URL = $Sitemap->addChild("url");
			$URL->addChild("loc", htmlspecialchars($url));
			$URL->addChild("priority", 0.4);
			$URL->addChild("changefreq", "never");
			$URL->addChild("lastmod", getNow());
		}
		$fp = fopen($source, (($isHeap) ? "w+" : "w"));
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? count($rows) : 0; // 傳回 XML 檔案是否建立成功
	}

	/**
	* 瘦身 threads 程式
	*
	* @param String $filename：檔案名稱
	* @return Boolean
	**/
	function slimming($filename) {
		if(!file_exists(XMLROOT . $filename)) return false;
		$xmls = simplexml_load_string(@file_get_contents(XMLROOT . $filename));
		if(empty($xmls)) return false;
		$rows = array();
		foreach($xmls as $xml) {
			array_push($rows, (string) $xml->loc); 
		}
		$rows = @array_unique($rows);
		if(count($rows) == 0) return false;
		return buildXML($filename, $rows);
	}

	/**
	* 建立 gzip 檔案 
	*
	* @param String $filename：XML 檔案名稱
	* @return Boolean
	**/
	function buildGZ($filename) {
		if(!file_exists(XMLROOT . $filename)) return false;
		$source = GZROOT . $filename . ".gz";
		$fp = gzopen($source, "w9"); // w9 = highest compression
		gzwrite ($fp, file_get_contents(XMLROOT . $filename));
		gzclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 gzip 檔案是否建立成功
	}

	/**
	* 解壓縮 gzip 檔案
	*
	* @param String $filename：XML 檔案名稱
	* @return Boolean
	**/
	function unGZ($filename) {

		$xml	= XMLROOT . $filename;
		$gz	= GZROOT  . $filename . ".gz";

		if(!file_exists($gz)) return false;

		$file = gzopen($gz, "rb", 0); 
		if($file) { 
			$data = ""; 
			while(!gzeof($file)) { 
				$data .= gzread($file, 1024); 
			} 
			gzclose($file); 
			if($data != "") {
				$fp = fopen($xml, "w");
				fwrite($fp, $data); 
				fclose($fp);
				return (file_exists($xml)) ? true : false;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	* 取得 W3C 時間
	*
	* @param none
	* @return W3C Format 時間
	**/
	function getNow() {
		$D = new DateTime("NOW");
		return $D->format(DateTime::W3C);
	}

	/**
	* 建立主要的 sitemap.xml 檔案
	*
	* @param Array &$rows：用來產生 threads 的資料陣列
	* @param String $sitemap：XML 檔案名稱
	* @param String $script_name：用來產生 XML 檔案的前置名稱，如 "rd-member"-forum.xml 中的 rd-member
	* @return Boolean 
	**/
	function buildMainXML(&$rows, $sitemap, $script_name) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $sitemap;
		$fp = fopen($source, "w");
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		foreach($rows as $row) {
			$url = $Sitemap->addChild("sitemap");
			$url->addChild("loc", WWWRoot . $script_name . "-" . $row . ".xml.gz");
			$url->addChild("lastmod", getNow());
		}
		$fp = fopen($source, "w");
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 XML 檔案是否建立成功
	}

	/**
	* 透過 curl 的方式提交搜尋引擎
	*
	* @param String $xml：提交的 XML 檔案網址
	* @return String message
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
?>
