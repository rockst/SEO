<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");

	// $type = "unknow";
	// $MongoColl = $MongoDB->$type;

	$xml_folder = "/output/yahoo/backup/unknow/";
	$count  = 0;
	$handle = opendir(dirname(__FILE__) . $xml_folder);
	while(($entry = readdir($handle)) !== false) {
		if(preg_match("/.xml$/i", $entry)) {
			echo $entry . ": \n";
			$file = file_get_contents(dirname(__FILE__) . $xml_folder . $entry, FILE_USE_INCLUDE_PATH);
			$xml  = simplexml_load_string($file, "SimpleXMLElement", LIBXML_NOCDATA);
			for($i = 0; $i < count($xml->addArticle); $i++) {
				if(preg_match("/^http:\/\/verywed.com\/forum\/essence\//i", $xml->addArticle[$i]->url)) {
					$type = "essence";
					$MongoColl = $MongoDB->essence;
				} else if(preg_match("/^http:\/\/verywed.com\/forum\/expexch\//i", $xml->addArticle[$i]->url)) {
					$type = "expexch";
					$MongoColl = $MongoDB->expexch;
				} else if(preg_match("/^http:\/\/verywed.com\/forum\/wedlife\//i", $xml->addArticle[$i]->url)) {
					$type = "wedlife";
					$MongoColl = $MongoDB->wedlife;
				}

				if($type) {
					$data = array();
					$data["key"]  = preg_replace("/\?utm_source=.*/i", "", (string) $xml->addArticle[$i]->url);
					$data["type"] = $type;
					$data["stage"]= 2;
					$data["msg"]  = "";
					$data["data"] = (array) $xml->addArticle[$i];
					$status = insert_mongodb($data);
					echo ($count + 1) . " - " . (string) $data["key"] . " " . $status . "\n";
					$count++;
				}
			}
			echo "Sleep...\n";
		}
	}
?>
