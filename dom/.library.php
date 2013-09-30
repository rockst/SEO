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
 		echo "產生 Sitemap Index: " . ((buildMainXML($projects, $index_name, $script_name)) ? "成功" : "失敗") . "\n";
		echo "FTP to beta: " . ((ftp2beta()) ? "success" : "fail") . "\n";
		echo "Rsync to online: " . ((rsync2online()) ? "success" : "fail") . "\n";
		echo "Submit " . WWWRoot . $index_name . "\n";
		SubmitSitemapCurl(WWWRoot . $index_name);
	}

	// 執行爬網址的主程式
	function sitemapProcess($path, $xml, $isHeap = false) {
		$start = getMicrotime();
		if(!file_exists(GZROOT . $xml . ".gz")) {
			file_put_contents(GZROOT . $xml . ".gz", file_get_contents(WWWRoot . $xml . ".gz"));
		}
   	$rows = array();
		$handle = opendir($path);
		while (false !== ($entry = readdir($handle))) {
			if(preg_match("/.php$/i", $entry) && !preg_match("/^simple_html_dom.php$/i", $entry)) {
				execPHP($rows, $path, $entry);
				if(count($rows) <= URLLIMIT) {
					if(unGZ(XMLROOT . $xml)) {
						if(buildXML($xml, $rows, $isHeap)) {
							if(slimming($xml)) {
								if(buildGZ($xml)) {
									unlink(XMLROOT . $xml); // 刪除 XML file
									if(filesize(GZROOT . $xml . ".gz") <= XMLSIZE) { 
										$message = "處理成功：總筆數 " . count($rows) . " 筆"; 
									} else { $message = "單一XML的檔案大小不能超過 " . XMLSIZE; }
								} else { $message = "建立 Gzip 檔案失敗"; }
							} else { $message = "瘦身 XML 檔案失敗"; }		
						} else { $message = "建立 XML 檔案失敗"; }		
					} else { $message = "解壓縮檔案失敗"; }
				} else { $message = "單一XML的筆數不能超過 " . URLLIMIT; }
			}
		}
		echo "===================================================\n";
		echo "訊息：" . $message . "\n";
		echo "總執行時間：" . (getMicrotime() - $start) . "\n";
		echo "===================================================\n";
		closedir($handle);
	}

   // FTP Sitemap 到 beta 機器
   function ftp2beta($filename = "") {
      $conn_id = ftp_connect(FTP_Server); // .config.php
      $login_result = ftp_login ($conn_id, FTP_USER, FTP_PAWD); // .account.php
      if($conn_id && $login_result) {
         if($filename != "") { // 單個檔案
            if(!ftp_put($conn_id, FTP_Path . $filename, GZROOT . $filename, FTP_BINARY)) {
               echo "FTP upload has failed!\n";
               return false;
            }
         } else { // 整個資料夾
            ftp_uploaddirectory($conn_id, GZROOT, FTP_Path);
         }
         ftp_quit($conn_id);
         return true;
      } else {
         if(!$conn_id)      echo "FTP connection has failed!\n";
         if(!$login_result) echo "Attempted to connect to " . FTP_Server . "for user " . FTP_USER . "\n";
         return false;
      }
   }

   // FTP 整個資料夾裡的檔案
   function ftp_uploaddirectory($conn_id, $local_dir, $remote_dir) {
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

   // 同步 beta 資料到線上
   function rsync2online() {
      // host=&rType=event&inpDir=sitemap&dName=sitemap
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, Rsync_URL); // .config.php
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
		return (file_exists($source)) ? $i : 0; // 傳回 XML 檔案是否建立成功
	}

	function slimming($filename) {
		if(!file_exists(XMLROOT . $filename)) return false;
		$xmls = simplexml_load_string(file_get_contents(XMLROOT . $filename));
		if(empty($xmls)) return false;
		$rows = array();
		foreach($xmls as $xml) {
			array_push($rows, (string) $xml->loc); 
		}
		$rows = @array_unique($rows);
		if(count($rows) == 0) return false;
		return buildXML($filename, $rows);
	}

	// 建立 gzip 檔案 
	function buildGZ($filename) {
		$source = GZROOT . $filename . '.gz';
		$fp = gzopen($source, 'w9'); // w9 = highest compression
		gzwrite ($fp, file_get_contents(XMLROOT . $filename));
		gzclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 gzip 檔案是否建立成功
	}

	function unGZ($source) {
		$file = gzopen($source . ".gz", "rb", 0); 
		if($file) { 
			$data = ""; 
			while(!gzeof($file)) { 
				$data .= gzread($file, 1024); 
			} 
			gzclose($file); 
			if($data != "") {
				$fp = fopen($source, 'w');
				fwrite($fp, $data); 
				fclose($fp);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function getNow() {
		$D = new DateTime('NOW');
		return $D->format(DateTime::W3C);
	}

	// 建立主要的 sitemap.xml 檔案
	function buildMainXML(&$rows, $sitemap, $script_name) {
		// 建立 XML 標頭資料 
		$source = XMLROOT . $sitemap;
		$fp = fopen($source, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
		fclose($fp);
		// 建立 XML 內容
		$Sitemap = new SimpleXMLElement($source, null, true);
		foreach($rows as $row) {
			$url = $Sitemap->addChild('sitemap');
			$url->addChild('loc', WWWRoot . $script_name . "-" . $row . ".xml.gz");
			$url->addChild('lastmod', getNow());
		}
		$fp = fopen($source, 'w');
		fwrite($fp, $Sitemap->asXML());
		fclose($fp);
		return (file_exists($source)) ? true : false; // 傳回 XML 檔案是否建立成功
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

?>
