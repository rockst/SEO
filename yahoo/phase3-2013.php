<?php
	// forum: essence, expexch, wedlife
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

	$MongoColl = $MongoDB->thread_20140613;
	$TPTZ = new DateTimeZone('Asia/Taipei');
	$D = new DateTime(date('Y-m-d', mktime(0,0,0,1,1,2000)), $TPTZ);
	$d1 = $D->getTimestamp();

	$D = new DateTime(date('Y-m-d', mktime(0,0,0,12,31,2013)), $TPTZ);
	$d2 = $D->getTimestamp();

	while(1) {
		$MongoCursor = $MongoColl->find(
			array(
				"stage"=> 3, 
				"msg"=> "", 
				"year"=> null,
				"type"=> new MongoRegex("/^(forum|essence)+$/i"), 
				"key"=>  new MongoRegex("/http:\/\/verywed.com\/forum\/(essence|expexch|wedlife)+/i"),
				"data.created_time" => array('$gte' => $d1, '$lte' => $d2)
			)
		)->limit(10000);
		$rows = array();
		$i = 0;
		foreach($MongoCursor as $document) {
			$status = update_mongodb($MongoColl, $document["_id"], array("year" => 2013));
			echo ($i + 1) . " - " . $document["key"] . " - Update: " . $status . "\n";
			array_push($rows, $document["data"]);
			$i++;
		}
		if(count($rows) > 0) {
			echo "Result: " . Yahoo_UGC::buildXML("tw_verywed_" . date("Ymd_His") . "_0", $rows) . "\n";
		} else {
			break;
		}
		sleep(1);
	}

?>
