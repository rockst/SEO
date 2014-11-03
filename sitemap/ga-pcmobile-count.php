<?php
	$Mongo = new MongoClient();
	$MongoDB = $Mongo->ga;
	$MongoColl1 = $MongoDB->device_201403;
	$MongoColl2 = $MongoDB->member_201403;

	$MongoCursor1 = $MongoColl1->find();
	foreach($MongoCursor1 as $document1) {

		$mem_id = $document1["mem_id"];

		echo $mem_id . " ";

		$cursor2   = $MongoColl2->find(array("mem_id"=>$mem_id));
		$document2 = $cursor2->getNext();
		$data = array("mem_id"=>$mem_id);

		if(!$document2["_id"]) {
			echo "insert: ";
			if($document1["device"] == "desktop") {
				$data["desktop"] = 1;
			// } else if($document1["device"] == "mobile" || $document1["device"] == "tablet") {
			} else {
				$data["mobile"]  = 1;
			}
			$status = ($MongoColl2->insert($data)) ? true : false;
		} else {
			echo "update: ";
			if($document1["device"] == "desktop") {
				$data["desktop"] = 1;
			// } else if($document1["device"] == "mobile" || $document1["device"] == "tablet") {
			} else {
				$data["mobile"]  = 1;
			}
			$status = $MongoColl2->update(array("_id" => $document2["_id"]), array('$set' => $data));
			$status = ($status["ok"]) ? true : false;
		}
		echo $status . "\n";
	}

?>
