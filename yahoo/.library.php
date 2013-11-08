<?php

	/**
	** 透過網址取得 URL 列表
	*
	* @param String $url
	* @param String $pattern
	* @param Int $limit
	*	@return Array $urls
	**/
	function get_url_list($url, $pattern, $limit = 1) {
		$urls = array();
		for($p = 1; $p <= $limit; $p++) {
			$html = file_get_html(preg_replace("/\{page\}/i", $p, $url));
			foreach($html->find($pattern) as $j=>$element) {
				array_push($urls, ((!preg_match("/^http:\/\/verywed.com/i", $element->href)) ? "http://verywed.com" : "") . $element->href);
			}
		}
		return array_unique($urls);
	}

	/**
	* FTP threads 到 Yahoo 機器，僅在開發環境適用
	*
	* @param String $filename（單個檔案）or ""（整個資料夾）
	* @return boolean
	**/
	function ftp2yahoo($filename = "") {
		$conn_id 		= ftp_connect(FTP_Yahoo_Server); // FTP_Server: .config.php
		$login_result 	= ftp_login($conn_id, FTP_Yahoo_USER, FTP_Yahoo_PAWD); // FTP_Yahoo_USER, FTP_Yahoo_PAWD: .account.php
		if($conn_id && $login_result) { 
			if($filename != "") { // 單個檔案 ftp 到 Yahoo 
				if(!ftp_put($conn_id, FTP_Yahoo_Path . $filename, XMLROOT . $filename, FTP_BINARY)) { 
					echo "FTP upload has failed!\n";
					return false;
				}
			} else { // 整個資料夾 ftp 到 Yahoo 
				ftp_uploaddirectory($conn_id, XMLROOT, FTP_Yahoo_Path);
			}
			ftp_quit($conn_id);
			return true;
		} else {
			if(!$conn_id) 		 echo "FTP connection has failed\n";
			if(!$login_result) echo "Attempted to connect to " . FTP_Yahoo_Server . " for user " . FTP_Yahoo_USER . "\n"; 
			return false;
		}
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
	* 建立 XML 檔案實體檔案
	*
	* @param String $filename (^[a-zA-Z0-9]+$)
	* @param Array $rows
	* @param Array $msg
	* @param Boolean $isHeap：是否用來疊加 XML 的變數
	* @return Int $num (threads 數量) or 0 (失敗)
	**/
	function buildXML($filename, &$rows, $isHeap = false) {

		include_once(dirname(__FILE__) . "/SimpleXMLEX.class.php");

		$Dom = new DOMDocument('1.0');
		$Dom->preserveWhiteSpace = false;
		$Dom->formatOutput = true;

		// 建立 XML 標頭資料 
		$source = XMLROOT . $filename . ".xml";
		if($isHeap == false) {
			$fp = fopen($source, 'w');
			fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><rawfeed version="1.0"></rawfeed>');
			fclose($fp);
		}

		// 建立 XML 內容
		// $XML = new SimpleXMLElement($source, null, true);
		$XML = new ExSimpleXMLElement($source, null, true);
		foreach($rows as $i=>$row) {
			$Thread = $XML->addChild("addArticle");
			while(list($key, $value) = each($row)) {
				if(gettype($value) == "array") {
					foreach($value as $data) {
         			$Thread->addChildCData($key, $data);
					}
				} else {
         		$Thread->addChildCData($key, $value);
				}
			}
		}
		$Dom->loadXML($XML->asXML());
		$fp = fopen($source, (($isHeap) ? "w+" : "w"));
		fwrite($fp, $Dom->saveXML());
		fclose($fp);

		if(file_exists($source)) {
			$fp = fopen(XMLROOT . $filename . ".done", "w");
			fwrite($fp, "");
			fclose($fp);
   		ftp2yahoo($filename . ".xml");
   		ftp2yahoo($filename . ".done");
			return true;
		} else {
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

	// strip javascript, styles, html tags, normalize entities and spaces
	// based on http://www.php.net/manual/en/function.strip-tags.php#68757
	function html2text($html){
		$text = $html;
		static $search = array(
			'@<script.+?</script>@usi',  // Strip out javascript content
			'@<style.+?</style>@usi',    // Strip style content
			'@<!--.+?-->@us',            // Strip multi-line comments including CDATA
			'@</?[a-z].*?\>@usi',         // Strip out HTML tags
		);
		$text = preg_replace($search, ' ', $text);
		// normalize common entities
		$text = normalizeEntities($text);
		// decode other entities
		$text = html_entity_decode($text, ENT_QUOTES, 'utf-8');
		// normalize possibly repeated newlines, tabs, spaces to spaces
		$text = preg_replace('/\s+/u', ' ', $text);
		$text = preg_replace('//u', '', $text);
		$text = trim($text);
		// we must still run htmlentities on anything that comes out!
		// for instance:
		// <<a>script>alert('XSS')//<<a>/script>
		// will become
		// <script>alert('XSS')//</script>
		return $text;
	} 

	// replace encoded and double encoded entities to equivalent unicode character
	// also see /app/bookmarkletPopup.js
	function normalizeEntities($text) {
		static $find = array();
		static $repl = array();
		if (!count($find)) {
			// build $find and $replace from map one time
			$map = array(
			array('\'', 'apos', 39, 'x27'), // Apostrophe
			array('\'', '‘', 'lsquo', 8216, 'x2018'), // Open single quote
			array('\'', '’', 'rsquo', 8217, 'x2019'), // Close single quote
			array('"', '“', 'ldquo', 8220, 'x201C'), // Open double quotes
			array('"', '”', 'rdquo', 8221, 'x201D'), // Close double quotes
			array('\'', '‚', 'sbquo', 8218, 'x201A'), // Single low-9 quote
			array('"', '„', 'bdquo', 8222, 'x201E'), // Double low-9 quote
			array('\'', '′', 'prime', 8242, 'x2032'), // Prime/minutes/feet
			array('"', '″', 'Prime', 8243, 'x2033'), // Double prime/seconds/inches
			array(' ', 'nbsp', 160, 'xA0'), // Non-breaking space
			array('-', '‐', 8208, 'x2010'), // Hyphen
			array('-', '–', 'ndash', 8211, 150, 'x2013'), // En dash
			array('--', '—', 'mdash', 8212, 151, 'x2014'), // Em dash
			array(' ', ' ', 'ensp', 8194, 'x2002'), // En space
			array(' ', ' ', 'emsp', 8195, 'x2003'), // Em space
			array(' ', ' ', 'thinsp', 8201, 'x2009'), // Thin space
			array('*', '•', 'bull', 8226, 'x2022'), // Bullet
			array('*', '‣', 8227, 'x2023'), // Triangular bullet
			array('...', '…', 'hellip', 8230, 'x2026'), // Horizontal ellipsis
			array('°', 'deg', 176, 'xB0'), // Degree
			array('€', 'euro', 8364, 'x20AC'), // Euro
			array('¥', 'yen', 165, 'xA5'), // Yen
			array('£', 'pound', 163, 'xA3'), // British Pound
			array('©', 'copy', 169, 'xA9'), // Copyright Sign
			array('®', 'reg', 174, 'xAE'), // Registered Sign
			array('™', 'trade', 8482, 'x2122'), // TM Sign
			);
			foreach ($map as $e) {
				for ($i = 1; $i < count($e); ++$i) {
					$code = $e[$i];
					if (is_int($code)) { // numeric entity
						$regex = "/&(amp;)?#0*$code;/";
					} elseif (preg_match('/^.$/u', $code)/* one unicode char*/) { // single character
						$regex = "/$code/u";
					} elseif (preg_match('/^x([0-9A-F]{2}){1,2}$/i', $code)) { // hex entity
						$regex = "/&(amp;)?#x0*" . substr($code, 1) . ";/i";
					} else { // named entity
						$regex = "/&(amp;)?$code;/";
					}
					$find[] = $regex;
					$repl[] = $e[0];
				}
			}
		} // end first time build
		return preg_replace($find, $repl, $text);	

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
