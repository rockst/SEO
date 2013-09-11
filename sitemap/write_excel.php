<?php
	ini_set("include_path", dirname(__FILE__) . "/ZendGdata/library");

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

	try {
		$SpreadsheetService = new Zend_Gdata_Spreadsheets(Zend_Gdata_ClientLogin::getHttpClient(USER, PAWD, Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME));
		$spreadsheets = lstSpreadsheets();
	} catch (Exception $e) {
		exit("Caught exception Zend_Gdata_Spreadsheets: " . $e->getMessage() . "\n");
	}

	$sitemaps = array();
	foreach($spreadsheets as $spreadsheet) {
		echo "- 【" . $spreadsheet["name"] . "】Spreadsheet：\n";
		$spreadsheetsKey = $spreadsheet["key"];
		$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
		$query->setSpreadsheetKey($spreadsheetsKey);
		$feed = $SpreadsheetService->getWorksheetFeed($query);
		$entry = $feed->entries[0];
		$set_worksheetId = basename($entry->id);
		$set_worksheet = getWorksheet($spreadsheetsKey, $set_worksheetId);
		foreach($set_worksheet as $i=>$worksheet) {
			echo "-- 【" . $worksheet["name"] . "】Worksheet：\n";
			foreach($feed->entries as $entry) {
				if($entry->title->text == $worksheet["name"]) {
					$msg	= array("IncorrectFormat"=>array(), "status"=>array());
					$rows = getWorksheet($spreadsheetsKey, basename($entry->id)); 
					$num  = buildXML2($worksheet["filename"], $rows, $spreadsheetsKey, basename($entry->id));
					if($num > 0) {
						$set_worksheet[$i]["num"] = $num;
						if(buildGZ($worksheet["filename"])) {
							$msg["status"][] = "success";
							echo "--- generate " . $num . " rows\n";
						} else {
							$msg["status"][] = "generate Gzip file fail";
							echo "--- " . $msg["status"][count($msg["status"])-1] . "\n";
						}
					} else {
						$msg["status"][] = "generate XML file fail";
						echo "--- " . $msg["status"][count($msg["status"])-1] . "\n";
					}
					try {
						$SpreadsheetService->updateCell(($i + 2), 3, date("Y/m/d"), $spreadsheetsKey, $set_worksheetId);
						$SpreadsheetService->updateCell(($i + 2), 4, join(",", $msg["status"]) . "\n", $spreadsheetsKey, $set_worksheetId);
						$SpreadsheetService->updateCell(($i + 2), 5, $num, $spreadsheetsKey, $set_worksheetId);
						$SpreadsheetService->updateCell(($i + 2), 6, join(",", $msg["IncorrectFormat"]), $spreadsheetsKey, $set_worksheetId);
					} catch (Exception $e) {
						exit("Caught exception: " . $e->getMessage() . "\n");
					}
				}
			}
		}
		$sitemaps = array_merge($sitemaps, $set_worksheet);
	}
	// 產生索引 Sitemap
	echo "Generate " . MainSitemap . ": " . ((buildMainXML2($sitemaps)) ? "success" : "fail") . "\n";
	// 提交搜尋引擎
	echo "- " . WWWRoot . MainSitemap . "\n";
	SubmitSitemapCurl(WWWRoot . MainSitemap);
	echo "- " . ROBOTSSITEMAP . "\n";
	SubmitSitemapCurl(ROBOTSSITEMAP);
?>
