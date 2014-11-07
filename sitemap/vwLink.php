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
		$spreadsheets = lstSpreadsheets("vwLink - test");
	} catch (Exception $e) { // 發生錯誤
		exit("Caught exception Zend_Gdata_Spreadsheets: " . $e->getMessage() . "\n");
	}
	if(!isset($spreadsheets)) {
		exit("無法連結 Google Doc Spreadsheets 或找不到任何資料\n");
	}

	// 建立 XML 標頭資料 
	$source = XMLROOT . "staichtml.xml";
	$fp = fopen($source, "w");
	fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
	fclose($fp);
	$Sitemap = new SimpleXMLElement($source, null, true);

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
		$data = array();
		foreach($set_worksheet as $i => $worksheet) { // 複數工作表
			while(list($key, $value) = each($worksheet)) {
				if(preg_match("/^時間戳記/", $key))			{ $data[$i]["created"] = trim($value); }
				else if(preg_match("/^該期網址/", $key))	{ $data[$i]["link"] = trim($value); }
				else if(preg_match("/^該期網頁中出現的照片網址以及關鍵字/", $key)) {
					$images  = explode("\n", $value);
					foreach($images as $image) {
						preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $image, $result);
						if(!empty($result[0])) {
							$data[$i]["imagestags"][] = array(
								$result[0],
								preg_replace('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', "", $image)
							);
						}
					}
				}
				else if(preg_match("/^標題/", $key))               { $data[$i]["title"] = trim($value); }
				else if(preg_match("/^該期上線日期/", $key)) 		{ $data[$i]["date"] = trim($value); }
				else if(preg_match("/^該期代表圖/", $key)) 			{ $data[$i]["image"] = trim($value); }
				else if(preg_match("/^期數/", $key)) 					{ $data[$i]["volumn"] = trim($value); }
				else if(preg_match("/^該期相關關鍵字/", $key))		{ $data[$i]["oTag"] = trim($value); }
				if(!empty($data[$i]["link"])) {
					if(preg_match("/magazine/i", $data[$i]["link"]))		$data[$i]["project"] = "magazine";
					else if(preg_match("/story/i", $data[$i]["link"]))		$data[$i]["project"] = "story";
				}
			}
			$Article = $Sitemap->addChild("addArticle");
			while(list($key, $value) = each($data[$i])) {
				if($key == "imagestags") $value = json_encode($value);
				$Article->addChild($key, $value);
			}
		}
		print_r($data);
	}

	$fp = fopen($source, "w");
	fwrite($fp, $Sitemap->asXML());
	fclose($fp);
?>
