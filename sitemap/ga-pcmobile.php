<?php

	/**
	** 這支程式主要的用途是從 GA 取得網頁資料用來產生 Sitemap
	** Rock Lin 09/04-13
	**/

	include_once(dirname(__FILE__) . "/.account.php"); // 設定 FTP
	include_once(dirname(__FILE__) . "/.config.php"); // 設定 Google Analytics 網站設定檔
	include_once(dirname(__FILE__) . "/.library.php"); // 引用常用的函式
	include_once(dirname(__FILE__) . "/library/gapi.class.php"); // Google Analytics PHP Interface

	// MongoDB
	$Mongo = new MongoClient();
	$MongoDB = $Mongo->ga;
	$MongoColl = $MongoDB->device_201403;

	function mongodb_insert_dc(&$MongoColl, $data) {

		$cursor = $MongoColl->find($data);
		$document = $cursor->getNext();
		if(!$document["_id"]) {
			return ($MongoColl->insert($data)) ? true : false;
		} else {
			return mongodb_update_dc($MongoColl, $document["_id"], $data);
		}

	}

	function mongodb_update_dc(&$MongoColl, &$MongoIDObj, $data) {
		$status = $MongoColl->update(array("_id" => $MongoIDObj), array('$set' => $data));
		return ($status["ok"]) ? true : false;
	}


	/* 可以不用輸入，預設拿取上週資料 */
	if(!empty($argv[1]) && !empty($argv[2])) {
		if(!_chkDate($argv[1]) || !_chkDate($argv[2]) || !_compDate($argv[1], $argv[2])) {
			echo <<<EOD
Please Input Argv:
Example: php ga.php 2012-01-01 2012-02-02 1000
- argv1: 開始日期 YYYY-MM-DD 
- argv2: 結束日期 YYYY-MM-DD 
- argv3: 一次拿到幾筆 \n
EOD;
			exit();
		} else {
			$date1 = $argv[1]; // 從輸入的 YYYY-MM-DD 開始抓資料
			$date2 = $argv[2]; // 從輸入的 YYYY-MM-DD 結束抓資料
		}
	} else { // 預設上週開始抓
		// 檢查日期格式
		list($date1, $date2) = get_befor_week_date(); // 取得上週日期
		if(!_chkDate($date1) || !_chkDate($date2) || !_compDate($date1, $date2)) {
			exit();
		}
	}
	echo "日期：" . $date1 . " ~ " . $date2 . "\n";
	// argv3: 預設一次拿幾筆資 
	$limit = (!empty($argv[3]) && intval($argv[3]) > 0) ? intval($argv[3]) : GALIMIT;
	// GAPI Object
	try {
		$ga = new gapi(USER, PAWD);
	} catch (Exception $e) {
		exit('GA 帳號錯誤: ' . $e->getMessage() . "\n");
	}
	$rows = array(); 
	foreach($GA_input2 as $input) {
		while(list($key, $value) = each($input)) { // 設定維度、指標、排序、過濾條件
			$$key = $value;
		}
		echo "- 處理" . $subject . "：\n";
		if(!isset($profile) || !isset($dimensions) || !isset($metrics) || !isset($sort)) {
			echo "-- 傳入的設定檔錯誤\n";
			break;
		}
		foreach($profile as $item) {
			echo "-- 關於『" . $item["name"] . "』資源.\n";
			$ga_id = $item["id"]; // GA ID
			$offset = 1; // 從第一個位置開始抓資料
			// 預設從 GA 拿回來的變數
			$totalCount = NULL; 		// 總筆數
			$gaResults = array(); 	// GA 資料陣列
			// 遞迴使用
			$cnt 	= 0;
			$counter = 0;
			$result_cnt = 0;
			// 取得 GA Server 傳回來的資料給 $gaResults 
			while(1) {
				$offset = (($counter * $limit) + 1);
				getGAResults($ga, $totalCount, $gaResults, $ga_id, $dimensions, $metrics, $sort, $filter, $date1, $date2, $offset, $limit);
				if(count($gaResults) == 0) break;
				foreach($gaResults as $result) {
					$row = array();
					foreach($dimensions as $j=>$funcname) {
						$funcname = "get" . $funcname;
						$dimensions_val = $result->$funcname();
						$dimensions_val = ($dimensions_type[$j] == "int") ? (int) $dimensions_val : (string) $dimensions_val;
						$row[$dimensions_key[$j]] = $dimensions_val; 
					}
					// echo "{$row["date"]} - {$row["mem_id"]} ({$row["device"]}): " . mongodb_insert_dc($MongoColl, $row) . "\n";
					 echo "{$row["mem_id"]} ({$row["device"]}): " . mongodb_insert_dc($MongoColl, $row) . "\n";
				}
				$counter++;
				if((($counter * $limit) + 1) > $totalCount) break;
				sleep(1);
			}
		}
		echo "totalCount: " . $totalCount . "\n";
	}
?>
