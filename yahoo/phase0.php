<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	$count = 0;
	$handle = opendir(dirname(__FILE__) . "/output/yahoo/backup/");
	while(($entry = readdir($handle)) !== false) {
		if(preg_match("/.xml$/i", $entry)) {
			echo $entry . ": \n";
			sleep(1);
			if(preg_match("/_(3|4)+.xml$/", $entry)) {
				$type = "blog";
			} else if(preg_match("/_(1|2|5|6|7|8)+.xml$/", $entry)) {
				$type = "forum";
			} else if(preg_match("/_0.xml$/", $entry)) {
				$type = "essence";
			} else {
				break;
			}
			$file = file_get_contents(dirname(__FILE__) . "/output/yahoo/backup/" . $entry, FILE_USE_INCLUDE_PATH);
			$xml  = simplexml_load_string($file, "SimpleXMLElement", LIBXML_NOCDATA);
			for($i = 0; $i < count($xml->addArticle); $i++) {
				$data = array();
				$data["key"] 	= preg_replace("/\?utm_source=.*/i", "", (string) $xml->addArticle[$i]->url);
				$data["type"] = $type;
				$data["stage"]= 2;
				$data["msg"]  = "";
				$data["data"] = (array) $xml->addArticle[$i];
				$status = insert_mongodb($data);
				echo ($count + 1) . " - " . (string) $data["key"] . " " . $status . "\n";
				$count++;
			}
			echo "Sleep...\n";
			sleep(1);
		}
	}
?>
