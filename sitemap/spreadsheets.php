<?php
	// 設定 Zend PHP 函式庫路徑
	ini_set("include_path", dirname(__FILE__) . "/library/ZendGdata/library");

	include_once(dirname(__FILE__) . "/.account.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");

	// 確認是否安裝 PHP Modules
	if(!chkModules("SimpleXML") || !chkModules("curl")) { // 否
		exit();
	}
	echo "\n";

	include_once("Zend/Loader.php");
	Zend_Loader::loadClass("Zend_Gdata");
	Zend_Loader::loadClass("Zend_Gdata_AuthSub");
	Zend_Loader::loadClass("Zend_Gdata_ClientLogin");
	Zend_Loader::loadClass("Zend_Gdata_Spreadsheets");

	// 連結 Google Doc Spreadsheets
	try {
		$SpreadsheetService = new Zend_Gdata_Spreadsheets(Zend_Gdata_ClientLogin::getHttpClient(USER, PAWD, Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME));
		$spreadsheets = lstSpreadsheets(SpreadsheetName);
	} catch (Exception $e) { // 發生錯誤
		exit("Caught exception Zend_Gdata_Spreadsheets: " . $e->getMessage() . "\n");
	}
	if(!isset($spreadsheets)) {
		exit("無法連結 Google Doc Spreadsheets 或找不到任何資料\n");
	}

	// 開始處理
	$sitemaps = array(); // Sitemap 索引檔資料陣列
	foreach($spreadsheets as $spreadsheet) { // 複數文件
		echo "- 【" . $spreadsheet["name"] . "】Spreadsheet：\n";
		$spreadsheetsKey = $spreadsheet["key"];
		$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
		$query->setSpreadsheetKey($spreadsheetsKey);
		$feed = $SpreadsheetService->getWorksheetFeed($query); // 取得工作表資料
		$entry = $feed->entries[0];
		$set_worksheetId = basename($entry->id); // 找到設定工作表資料
		$set_worksheet = getWorksheet($spreadsheetsKey, $set_worksheetId);
		foreach($set_worksheet as $i=>$worksheet) { // 複數工作表
			echo "-- 【" . $worksheet["name"] . "】Worksheet：";
			$is_match = false;
			foreach($feed->entries as $entry) { // 處理工作表中每一列資料
				if($entry->title->text == $worksheet["name"]) { // 比對設定工作表中 name 欄位是否與工作表名稱一樣
					$is_match = true;
					$msg	= array("IncorrectFormat"=>array(), "status"=>array());
					$rows = getWorksheet($spreadsheetsKey, basename($entry->id)); 
					$num  = buildXML($worksheet["filename"], $rows); // 產生 XML file
					if($num > 0) {
						if(buildGZ($worksheet["filename"])) { // 產生 Gzip file
							unlink(XMLROOT . $worksheet["filename"]); // 刪除 XML file
							$set_worksheet[$i]["num"] = $num;
							$msg["status"][] = "success";
							echo "產生 " . $num . " threads\n";
						} else { // 產生 Gzip file 失敗
							$msg["status"][] = "generate Gzip file fail";
							echo "\n--- " . $msg["status"][count($msg["status"])-1] . "\n";
						}
					} else { // 產生 XML file 失敗
						$msg["status"][] = "generate XML file fail";
						echo "\n--- " . $msg["status"][count($msg["status"])-1] . "\n";
					}
					// 寫回 Google Doc 設定工作表狀況
					try {
						$SpreadsheetService->updateCell(($i + 2), 3, date("Y/m/d"), $spreadsheetsKey, $set_worksheetId); // run date
						$SpreadsheetService->updateCell(($i + 2), 4, join(",", $msg["status"]) . "\n", $spreadsheetsKey, $set_worksheetId); // status
						$SpreadsheetService->updateCell(($i + 2), 5, $num, $spreadsheetsKey, $set_worksheetId); // 產生比數
						$SpreadsheetService->updateCell(($i + 2), 6, join(",", $msg["IncorrectFormat"]), $spreadsheetsKey, $set_worksheetId); // 格式錯誤: 列數,行數
					} catch (Exception $e) {
						exit("Caught exception: " . $e->getMessage() . "\n");
					}
				} 
			}
			if($is_match == false) { 
				echo "找不到相關工作表\n"; 
			}
		}
		$sitemaps = array_merge($sitemaps, $set_worksheet); // 集合 Sitemap 索引檔資料
		echo "\n";
	}
	// build Sitemap index
	echo "Generate " . MainSitemap . ": " . ((buildMainXML($sitemaps)) ? "success" : "fail") . "\n";
	// FTP
	echo "FTP to beta: " . ((ftp2beta()) ? "success" : "fail") . "\n";
	// Rsync
	echo "Rsync to online: " . ((rsync2online()) ? "success" : "fail") . "\n";
	// Submit 
	echo "- " . WWWRoot . MainSitemap . "\n";
	// SubmitSitemapCurl(WWWRoot . MainSitemap . "?" . date("Ymd"));
	SubmitSitemapCurl(WWWRoot . MainSitemap);
	echo "- " . ROBOTSSITEMAP . "\n";
	// SubmitSitemapCurl(ROBOTSSITEMAP . "?" . date("Ymd"));
	SubmitSitemapCurl(ROBOTSSITEMAP);
?>
