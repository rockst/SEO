<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

	$MongoCursor = $MongoColl->find(array("stage"=>2));
	$rows = array();
	$i = 0;
	foreach($MongoCursor as $document) {
		$document["data"]["created_time"] = (int) $document["data"]["created_time"];
		$document["data"]["updated_time"] = (int) $document["data"]["updated_time"];
		$status = update_mongodb($MongoColl, $document["_id"], array("data"=> $document["data"]));
		echo ($i + 1) . " - " . $document["key"] . " - Update: " . $status . "\n";
		$i++;
	}
?>
