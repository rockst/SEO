<?php
	// forum: essence, expexch, wedlife
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

	while(1) {
		$MongoCursor = $MongoColl->find(
			array(
				"stage"=> 3, 
				"msg"=> "", 
				"type"=> new MongoRegex("/^(forum|essence)+$/i"), 
				"key"=>  new MongoRegex("/http:\/\/verywed.com\/forum\/(essence|expexch|wedlife)+/i"),
				// "data.title"=> new MongoRegex("/veryWed聲明/i")
				// "data.title"=> new MongoRegex("/^Hi,/i")
				// "data.body"=> new MongoRegex("/veryWed聲明/i")
				// "data.body"=> new MongoRegex("/Hi,發言時遵守討論區服務條款/i")
				"data.body"=> new MongoRegex("/\*Hi,/i")
			)
		)->limit(10000);
		$rows = array();
		$i = 0;
		foreach($MongoCursor as $document) {
			$status = update_mongodb($MongoColl, $document["_id"], array("msg" => "warring"));
			echo ($i + 1) . " - " . $document["key"] . " - Update: " . $status . "\n";
			array_push($rows, $document["data"]);
			$i++;
		}
		/*
		if(count($rows) > 0) {
			echo "Result: " . Yahoo_UGC::removeThread("tw_verywed_" . date("Ymd_His") . "_0", $rows) . "\n";
		} else {
			break;
		}
		*/
		echo "Sleep ...\n";
		sleep(2);
	}
?>
