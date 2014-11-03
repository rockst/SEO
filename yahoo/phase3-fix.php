<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

	$MongoColl = $MongoDB->essence;

	while(1) {
		$MongoCursor = $MongoColl->find(array("stage"=>2))->limit(10000);
		$rows = array();
		$i = 0;
		foreach($MongoCursor as $document) {
			$status = update_mongodb($MongoColl, $document["_id"], array("stage" => 3));
			echo ($i + 1) . " - " . $document["key"] . " - Update: " . $status . "\n";
			array_push($rows, $document["data"]);
			$i++;
		}
		if(count($rows) > 0) {
			echo "Result: " . Yahoo_UGC::buildXML("tw_verywed_" . date("Ymd_His") . "_0", $rows) . "\n";
		} else {
			break;
		}
		echo "Sleep ...\n";
		sleep(2);
	}
?>
