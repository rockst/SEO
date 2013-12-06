<?php

	/**
	** 透過網址取得 URL 列表
	*
	* @param String $url
	* @param String $pattern
	* @param Int $limit
	* @param String $type
	*	@return Array $urls
	**/
	function get_url_list($list, $pattern, $index = 1, $limit = 1, $type = "") {
		global $argv;

		$urls = array();

		$first = (intval($index) > 0) ? intval($index) : 1;
		$limit = $first + $limit;

		if(!preg_match("/\{page\}/", $list)) {
			$html = file_get_html($list);
			$i = 0;
			foreach($html->find($pattern) as $element) {
				$url = ((!preg_match("/^http:\/\/verywed.com/i", $element->href)) ? "http://verywed.com" : "") . $element->href;
				if($type != "") {
					echo ($i + 1) . " - " . $url . ": " . insert_mongodb(array("key"=>$url, "type"=>$type, "stage"=>1, "msg"=>"")) . "\n";
				} else {
					array_push($urls, $url);
				}
				$i++;
			}
		} else {
			$i = 0;
			for($p = $first; $p <= $limit; $p++) {
				$html = file_get_html(preg_replace("/\{page\}/i", $p, $list));
				foreach($html->find($pattern) as $element) {
					$url = ((!preg_match("/^http:\/\/verywed.com/i", $element->href)) ? "http://verywed.com" : "") . $element->href;
					if($type != "") {
						echo ($i + 1) . " - " . $url . ": " . insert_mongodb(array("key"=>$url, "type"=>$type, "stage"=>1, "msg"=>"")) . "\n";
					} else {
						array_push($urls, $url);
					}
					$i++;
				}
			}
		}

		if($type == "") {
			return array_unique($urls);
		}

	}

	function insert_mongodb($data) {
		GLOBAL $MongoColl;

		$cursor 	= $MongoColl->find(array("key"=>$data["key"]));
		$document = $cursor->getNext();
		if(!$document["_id"]) {
			return ($MongoColl->insert($data)) ? true : false;
		} else {
			return update_mongodb($MongoColl, $document["_id"], $data); 
		}

	}

	function update_mongodb(&$MongoColl, &$MongoIDObj, $data) {

		$status = $MongoColl->update(array("_id" => $MongoIDObj), array('$set' => $data));
		return ($status["ok"]) ? true : false;

	}

	function phase2_error($msg) {
		GLOBAL $MongoColl, $MongoIDObj;

		echo "-- " . $msg . "\n";
		return update_mongodb($MongoColl, $MongoIDObj, array("msg" => $msg));

	}

	/**
	* FTP 整個資料夾裡的檔案
	*
	* @param Object $conn_id
	* @param String $local_dir
	* @param String $remote_dir
	* @return none
	**/
	function ftp_uploaddirectory($conn_id, $local_dir, $remote_dir) {
		@ftp_mkdir($conn_id, $remote_dir);
		$handle = opendir($local_dir);
		while(($file = readdir($handle)) !== false) {
			if(($file != '.') && ($file != '..')) {
				if(is_dir($local_dir . $file)) {
					ftp_uploaddirectory($conn_id, $local_dir . $file . '/', $remote_dir . $file . '/');
				} else {
					$f[] = $file;
				}
			}
		}
		closedir($handle);
		if(count($f)) {
			sort($f);
			ftp_chdir($conn_id, $remote_dir);
			foreach ($f as $files) {
				$from = fopen($local_dir . $files, 'r');
				ftp_fput($conn_id, $files, $from, FTP_BINARY);
			}
		}
	}

	/**
	* 確認是否安裝 PHP Modules
	*
	* @param String $name
	* @return boolean
	**/
	function chkModules($name) {
		if(extension_loaded($name)) {
			return true;
		} else {
			echo "Install " . $name . " PHP Modules? No\n";
			return false;
		}
	}

	/** 
	* The same thing than implode function, but return the keys so 
	* 
	* <code> 
	* $_GET = array('id' => '4587','with' => 'key'); 
	* ... 
	* echo shared::implode_with_key('&',$_GET,'='); // Resultado: id=4587&with=key 
	* ... 
	* </code> 
	* 
	* @param string $glue Oque colocar entre as chave => valor 
	* @param array $pieces Valores 
	* @param string $hifen Separar chave da array do valor 
	* @return string 
	* @author memandeemail at gmail dot com 
	*/ 
	function implode_with_key($glue = null, $pieces, $hifen = ',') { 
		$return = null; 
		foreach ($pieces as $tk => $tv) $return .= $glue.$tk.$hifen.$tv; 
		return substr($return,1); 
	}

	/** 
	* Return unique values from a tree of values 
	* 
	* @param array $array_tree 
	* @return array 
	* @author memandeemail at gmail dot com 
	*/
	function array_unique_tree($array_tree) { 
		$will_return = array(); $vtemp = array(); 
		foreach ($array_tree as $tkey => $tvalue) $vtemp[$tkey] = implode_with_key('&',$tvalue,'='); 
		foreach (array_keys(array_unique($vtemp)) as $tvalue) $will_return[$tvalue] = $array_tree[$tvalue]; 
		return $will_return; 
	}

	/**
	* 取得現在的日期時間
	*
	* @return W3C Datetime
	**/
	function getNow() {
		$D = new DateTime("NOW");
		return $D->format(DateTime::W3C);
	}

	/**
	* 執行 PHP 檔案取得 URL
	*
	* @param Array &$rows：抓取到網址的資料陣列
	* @param String $path：工具程式位置
	* @param String $name：工具程式名稱
	* @return boolean
	**/
	function execPHP($file) {
		$file = dirname(__FILE__) . "/" . $file;
		exec(escapeshellcmd("/usr/bin/php " . $file), $output);
		return $output[0];
	}

?>
