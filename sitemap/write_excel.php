<?php
	ini_set("include_path", dirname(__FILE__) . "/ZendGdata/library");
	include_once(dirname(__FILE__) . "/.account.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once("Zend/Loader.php");

	Zend_Loader::loadClass("Zend_Gdata");
	Zend_Loader::loadClass("Zend_Gdata_AuthSub");
	Zend_Loader::loadClass("Zend_Gdata_ClientLogin");
	Zend_Loader::loadClass("Zend_Gdata_Spreadsheets");

	$SpreadsheetService = new Zend_Gdata_Spreadsheets(Zend_Gdata_ClientLogin::getHttpClient(USER, PAWD, Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME));
	$spreadsheets = lstSpreadsheets();

	foreach($spreadsheets as $spreadsheet) {

		echo "開始處理【" . $spreadsheet["name"] . "】文件：\n";
		Sleep(1);

		// Get spreadsheet key
		$spreadsheetsKey = $spreadsheet["key"];

		$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
		$query->setSpreadsheetKey($spreadsheetsKey);
		$feed = $SpreadsheetService->getWorksheetFeed($query);

		$entry = $feed->entries[0];
		$set_worksheetId = basename($entry->id);

		$set_worksheet = getWorksheet($spreadsheetsKey, $set_worksheetId);

		foreach($set_worksheet as $i=>$worksheet) {
			echo "-- 開始處理【" . $worksheet["name"] . "】工作表：\n";
			Sleep(1);
			foreach($feed->entries as $entry) {
				if($entry->title->text == $worksheet["name"]) {
					$rows = getWorksheet($spreadsheetsKey, basename($entry->id)); 
					buildXML2($worksheet["filename"], $rows, $spreadsheetsKey, basename($entry->id));
					$updatedCell = $SpreadsheetService->updateCell(($i + 2), 3, getNow(), $spreadsheetsKey, $set_worksheetId);
				}
			}
		}
	}
?>
