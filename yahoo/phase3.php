<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");
	$type_array = array("forum", "essence", "blog", "ven_blog");
	while(1) {
		$type = $type_array[rand(0, (count($type_array) - 1))];
		echo $type . "\n";
		$MongoCursor = $MongoColl->find(array("stage"=>2, "type"=>$type))->limit(10000);
		$rows = array();
		$i = 0;
		foreach($MongoCursor as $document) {
			$status = update_mongodb($MongoColl, $document["_id"], array("stage" => 3));
			echo ($i + 1) . " - " . $document["key"] . " - Update: " . $status . "\n";
			array_push($rows, $document["data"]);
			$i++;
		}
		if(count($rows) > 0) {
			echo "Result: " . Yahoo_UGC::buildXML("tw_verywed_" . date("Ymd_His") . "_9", $rows) . "\n";
		}
		echo "Sleep ...\n";
		sleep(3);
	}
?>
