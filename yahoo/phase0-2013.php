<?php

	// 匯入舊的資料
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");

	$xml_folder = "/output/yahoo/2013/";
	$count  = 0;
	$handle = opendir(dirname(__FILE__) . $xml_folder);
	while(($entry = readdir($handle)) !== false) {
		if(preg_match("/.xml$/i", $entry)) {
			echo $entry . ": \n";
			$file = file_get_contents(dirname(__FILE__) . $xml_folder . $entry, FILE_USE_INCLUDE_PATH);
			$xml  = simplexml_load_string($file, "SimpleXMLElement", LIBXML_NOCDATA);
			for($i = 0; $i < count($xml->addArticle); $i++) {

				if(preg_match("/^http:\/\/verywed.com\/forum\/essence/i", $xml->addArticle[$i]->url)) {
					$type = "essence";
				} else if(preg_match("/^http:\/\/verywed.com\/forum/i", $xml->addArticle[$i]->url)) {
					$type = "forum";
				} else if(preg_match("/^http:\/\/verywed.com\/blog/i", $xml->addArticle[$i]->url)) {
					$type = "ven_blog";
				} else if(preg_match("/^http:\/\/verywed.com\/vwblog/i", $xml->addArticle[$i]->url)) {
					$type = "blog";
				}

				if($type) {
					$data = array();
					$data["key"]  = preg_replace("/\?utm_source=.*/i", "", (string) $xml->addArticle[$i]->url);
					$data["type"] = $type;
					$data["stage"]= 2;
					$data["msg"]  = "";
					if(!empty($xml->addArticle[$i]->url) && isset($xml->addArticle[$i]->url)) {
						$xml->addArticle[$i]->url = preg_replace("/\?utm_source=.*/i", "?src=yahoo_ugc", (string) $xml->addArticle[$i]->url);
					}
					if(!empty($xml->addArticle[$i]->thread_url) && isset($xml->addArticle[$i]->thread_url)) {
						$xml->addArticle[$i]->thread_url = preg_replace("/\?utm_source=.*/i", "?src=yahoo_ugc", (string) $xml->addArticle[$i]->thread_url);
					}
					if(!empty($xml->addArticle[$i]->blog_url) && isset($xml->addArticle[$i]->blog_url)) {
						$xml->addArticle[$i]->blog_url = preg_replace("/\?utm_source=.*/i", "?src=yahoo_ugc", (string) $xml->addArticle[$i]->blog_url);
					}
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
