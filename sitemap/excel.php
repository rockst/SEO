<?php
	ini_set("include_path", dirname(__FILE__) . "/ZendGdata/library");

	include_once(dirname(__FILE__) . "/.account.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");

	echo "執行開始時間：" . getNow() . "\n\n";

	// 確認是否安裝 PHP Modules
	if(!chkModules("SimpleXML") || !chkModules("curl")) { // 否
		exit();
	}
	echo "\n";

	// Include the loader and Google API classes for spreadsheets
	include_once("Zend/Loader.php");
	Zend_Loader::loadClass("Zend_Gdata");
	Zend_Loader::loadClass("Zend_Gdata_ClientLogin");
	Zend_Loader::loadClass("Zend_Gdata_Spreadsheets");
	Zend_Loader::loadClass("Zend_Http_Client");

	try {
		// Authenticate on Google Docs and create a Zend_Gdata_Spreadsheets object.            
		$spreadsheetService = new Zend_Gdata_Spreadsheets(Zend_Gdata_ClientLogin::getHttpClient(USER, PAWD, Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME));
		$query = new Zend_Gdata_Spreadsheets_ListQuery();
	} catch (Exception $e) {
		exit('Caught exception: ' . $e->getMessage() . "\n");
	}

	// 處理多個文件
	$info_array = array(); // 用來產生主要的 sitemap.xml 檔案
	foreach($GSheetIDs as $GSheetID) {
		echo "開始處理文件：" . $GSheetID . "\n";
		try {
			// 取得文件中『第一個工作表』的設定資料
			$query->setSpreadsheetKey($GSheetID);
			$info = getWorkFeedNumbs($spreadsheetService, $query);
		} catch (Exception $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
			break;
		}
		if(!$info || count($info) === 0) {
			echo '- 沒有任何的工作表需要被轉換成 XML 檔案\n';
			break;
		}

		$rownum = count($info); // 該工作表的 URL 總數
		$total = 0; 				// 所有工作表的 URL 總數

		echo "- 總工作表數量: " . count($info) . "\n";
		for($i = 0; $i < count($info); $i++) {
			echo ($i + 1) . ": " . $info[$i][0] . ": \n";
			// 檢查 XML 檔名格式
			if(_chkXML($info[$i][1])) {
				echo "-- 檢查檔名: " . $info[$i][1] . " 沒問題\n";
				$info[$i][2] = 0;
				if(!preg_match(FILTERWORKNAME, $info[$i][1])) { 

					// 取得工作表內的資料
					$query->setWorksheetId($i + 2); // 從第 2 張工作表開始
					try {
						$listFeed = $spreadsheetService->getListFeed($query);
					} catch (Exception $e) {
						echo "-- Caught exception: " . $e->getMessage() . "\n";
						break;
					}

					$rows = array(); // 預設工作表內每一列的資料陣列

					// 處理資料陣列 $row 用來 建立 XML 檔案 
					getWorksheetData($listFeed, $rows); 
					unset($listFeed);

					if(count($rows) > 0) {

						// 建立 XML 檔案
						$rownum = buildXML($info[$i][1], $rows); 
						unset($rows);

						if(intval($rownum) > 0) {
							echo "-- 產生 " . $info[$i][1] . " 檔案成功，共 " . $rownum . " 筆資料\n";
							// 建立 gzip 檔案
							if(buildGZ($info[$i][1])) { 
								echo "-- 建立 " . $info[$i][1] . ".gz 檔案成功\n";
								$xml = WWWRoot . $info[$i][1] . ".gz"; 
								$info[$i][2] = $rownum; // 記錄 URL 數
							} else {
								echo "-- 建立 " . $info[$i][1] . ".gz 檔案失敗\n";
							}
						} else {
							echo "-- 產生 " . $info[$i][1] . " XML 檔案失敗\n";
						}
					} else {
						echo "-- Google 試算工作表無任何資料\n";
					}
				} else {
					$info[$i][2] = 1;
				}
			} else {
				echo "-- 檢查檔名: " . $info[$i][1] . " 不合法\n";
			}
			sleep(1);
			$total += $rownum;
		}
		echo "==================================\n";
		echo "- 共產生了 " . $total . " 筆 URL\n";
		echo "==================================\n";
		$info_array = array_merge($info_array, $info); // 用來產生主要 sitemap.xml 的資料陣列
		unset($GSheetID);
	}
	unset($info);

	// 產生主要 sitemap.xml 檔案
	echo "產生主要的 " . MainSitemap . ": " . ((buildMainXML($info_array)) ? "成功" : "失敗") . "\n";
	// 提交搜尋引擎
	echo "- " . WWWRoot . MainSitemap . "\n";
	SubmitSitemapCurl(WWWRoot . MainSitemap);
	echo "- " . ROBOTSSITEMAP . "\n";
	SubmitSitemapCurl(ROBOTSSITEMAP);
	echo "執行結束時間：" . getNow() . "\n\n";
?>
