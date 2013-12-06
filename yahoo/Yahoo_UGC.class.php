<?php
Class Yahoo_UGC {
		public $url; // 基本的索引單位
		public $blog_url; // blog_url 同屬於某一個部落格主頁
		public $thread_url; // thread_url 同屬於某一個討論串
		public $page_no; // 所屬討論串/討論主題之第幾頁，但部落格文章比較少見，請放1
		public $content_type ; // 值必為 forum 或 blog
		public $language = "zh-Hant"; // 指明提交文件的語言，請採用BCP-47 語言標籤格式, zh-Hant
		public $region = "TW"; // 指明提供服務之主要地區，請採用ISO-3166-1 alpha-2碼, TW
		public $title; // 文件標題須為純文字
		public $body; // 文件內容須為純文字
		public $blog_title; // 部落格文章所屬部落格之標題。
		public $site_name = "verywed.com"; // 所屬網站的 domain name
		public $created_time; // unix timestamp，此文件之建立時間
		public $updated_time; // unix timestamp，此文件之最後更新時間
		public $category = array(); // 文件所屬之站內的分類架構，可重複
		public $creator; // 使用者暱稱
		public $no_of_replies; // 總回應數
		public $no_of_views; // 總瀏覽數
		public $tag = array(); // 文件之相關標籤，可重複
		public $title_raw; // 文件標題之raw html，用於anti-spam判斷輔助
		public $body_raw; // 文件內容之raw html，用於anti-spam判斷輔助
		public $blog_description_raw; // 文件內容之raw thml，用於anti-spam判斷輔助，主要用於部落格文章
		public $creator_ip; // 文件建立者之IP，用於anti-spam判斷輔助
		public $rating_score; // 站上使用者給予此文件之評分分數，需為五點式評分，例如3.5或4
		public $rating_count; // 明站上使用者給予此文件評分之總人數，若rating_score有提供則rating_count為必填
		public $author_type; // 值必為top_blogger, talents或celebrity
		public $author_thumb; // 文件建立者在站上使用之大頭貼，格式為URL
		public $quality_type; // unbox 意為開箱文、featured意為編輯推薦或站內新聞、collections為精華文。
		public $image = array(); // 文件內所內嵌之相關圖片URL，可重複

		public function __construct($url = "",$blog_url = "",$thread_url = "",$page_no = "",$content_type  = "",$title = "",$body = "",$blog_title = "",$created_time = "",$updated_time = "",$category = array(),$creator = "",$no_of_replies = "",$no_of_views = "",$tag = array(),$title_raw = "",$body_raw = "",$blog_description_raw = "",$creator_ip = "",$rating_score = "",$rating_count = "",$author_type = "",$author_thumb = "",$quality_type = "",$image = array()) {

			$this->set_url($url);
			$this->set_blog_url($blog_url);
			$this->set_thread_url($thread_url);
			$this->set_page_no($page_no);
			$this->set_content_type($content_type); 
			$this->set_title($title);
			$this->set_body($body);
			$this->set_blog_title($blog_title);
			$this->set_created_time($created_time);
			$this->set_updated_time($updated_time);
			$this->set_category($category);
			$this->set_creator($creator);
			$this->set_no_of_replies($no_of_replies);
			$this->set_no_of_views($no_of_views);
			$this->set_tag($tag);
			$this->set_title_raw($title_raw);
			$this->set_body_raw($body_raw);
			$this->set_blog_description_raw($blog_description_raw);
			$this->set_creator_ip($creator_ip);
			$this->set_rating_score($rating_score);
			$this->set_rating_count($rating_count);
			$this->set_author_type($author_type);
			$this->set_author_thumb($author_thumb);
			$this->set_quality_type($quality_type);
			$this->set_image($image);

		}

		public function set_url($url = "") {

			$this->url = trim($url) . "?utm_source=yahoo&utm_medium=ugc";

		}

		public function set_blog_url($blog_url = "") {

			if(preg_match("/^http(s)*:\/\/verywed.com/i", $blog_url)) {
				$this->blog_url = trim($blog_url) . "?utm_source=yahoo&utm_medium=ugc";
				return true;
			} else {
				return false;
			}

		}

		public function set_thread_url($thread_url = "") {

			if(preg_match("/^http(s)*:\/\/verywed.com/i", $thread_url)) {
				$this->thread_url = trim($thread_url) . "?utm_source=yahoo&utm_medium=ugc";
				return true;
			} else {
				return false;
			}

		}

		public function set_page_no($page_no = "") {

			$this->page_no = trim($page_no);

		}

		public function set_content_type($content_type = "") {

			$this->content_type = trim($content_type);

		}
 
		public function set_language($language = "") {

			$this->language = trim($language);

		}

		public function set_region($region = "") {

			$this->region = trim($region);

		}

		public function set_title($title = "") {

			$this->title = preg_replace("/\s+/u", "", self::html2text($title));

		}

		public function set_body($body = "") {

			$this->body = preg_replace("/\s+/u", "", self::html2text($body));

		}

		public function set_blog_title($blog_title = "") {

			if(!empty($blog_title)) {
				$this->blog_title = trim($blog_title);
				return true;
			} else {
				return false;
			}

		}

		public function set_site_name($site_name = "") {

			$this->site_name = trim($site_name);

		}

		public function set_created_time($created_time = "") {

			$this->created_time = trim($created_time);

		}

		public function set_updated_time($updated_time = "") {

			$this->updated_time = trim($updated_time);

		}

		public function set_category($category = array()) {

			$this->category = $category;

		}

		public function set_creator($creator = "") {

			if(!empty($creator)) {
				$this->creator = self::html2text($creator);
				return true;
			} else {
				return false;
			}

		}

		public function set_no_of_replies($no_of_replies = "") {

			$this->no_of_replies = trim($no_of_replies);

		}

		public function set_no_of_views($no_of_views = "") {

			$this->no_of_views = trim($no_of_views);

		}

		public function set_tag($tag = array()) {

			$this->tag = $tag;

		}

		public function set_title_raw($title_raw = "") {

			$this->title_raw = trim($title_raw);

		}

		public function set_body_raw($body_raw = "") {

			$this->body_raw = trim($body_raw);

		}

		public function set_blog_description_raw($blog_description_raw = "") {

			$this->blog_description_raw = trim($blog_description_raw);

		}

		public function set_creator_ip($creator_ip = "") {

			$this->creator_ip = trim($creator_ip);

		}

		public function set_rating_score($rating_score = "") {

			$this->rating_score = trim($rating_score);

		}

		public function set_rating_count($rating_count = "") {

			$this->rating_count = trim($rating_count);

		}

		public function set_author_type($author_type = "") {

			$this->author_type = trim($author_type);

		}

		public function set_author_thumb($author_thumb = "") {

			$this->author_thumb = trim($author_thumb);

		}

		public function set_quality_type($quality_type = "") {

			$this->quality_type = trim($quality_type);

		}

		public function set_image($image = array()) {

			$this->image = $image;

		}

		public function xml_mapping() {

			$row = array();
			while(list($key, $value) = each($this)) {
				if(preg_match("/(" . preg_replace("/,/", "|", $this->xml_mapping) . ")+/", $key)) {
					if(!empty($value)) {
						$row[$key] = $value;
					}
				}
			}
			return $row;

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
		public function buildXML($filename, &$rows, $isHeap = false) {
			include_once(dirname(__FILE__) . "/.config.php");
			include_once(dirname(__FILE__) . "/SimpleXMLEX.class.php");

			$Dom = new DOMDocument("1.0");
			$Dom->preserveWhiteSpace = false;
			$Dom->formatOutput = true;

			// 建立 XML 標頭資料 
			$source = XMLROOT . $filename . ".xml";
			if($isHeap == false) {
				$fp = fopen($source, "w");
				fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?><rawfeed version="1.0"></rawfeed>');
				fclose($fp);
			}

			// 建立 XML 內容
			$XML  = new ExSimpleXMLElement($source, null, true);
			foreach($rows as $i=>$row) {
				$Thread = $XML->addChild("addArticle");
				while(list($key, $value) = each($row)) {
					if(gettype($value) == "array") {
						foreach($value as $data) $Thread->addChildCData($key, $data);
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
   			self::ftp2yahoo($filename . ".xml");
   			self::ftp2yahoo($filename . ".done");
				return true;
			} else {
				return false;
			}

		}

		/**
			* FTP threads 到 Yahoo 機器，僅在開發環境適用
			*
			* @param String $source
			* @return boolean
		**/
		public function ftp2yahoo($filename) {
			include_once(dirname(__FILE__) . "/.config.php");
			include_once(dirname(__FILE__) . "/.account.php");

			$conn_id 				= ftp_connect(FTP_Yahoo_Server); // FTP_Server: .config.php
			$login_result 	= ftp_login($conn_id, FTP_Yahoo_USER, FTP_Yahoo_PAWD); // FTP_Yahoo_USER, FTP_Yahoo_PAWD: .account.php

			if($conn_id && $login_result) { 
				if($filename != "") { // 單個檔案 ftp 到 Yahoo 
					if(!ftp_put($conn_id, FTP_Yahoo_Path . $filename, XMLROOT . $filename, FTP_BINARY)) { 
						echo "FTP upload has failed!\n";
						return false;
					}
				}
				ftp_quit($conn_id);
				return true;
			} else {
				if(!$conn_id) 		 echo "FTP connection has failed\n";
				if(!$login_result) echo "Attempted to connect to " . FTP_Yahoo_Server . " for user " . FTP_Yahoo_USER . "\n"; 
				return false;
			}

		}

		// strip javascript, styles, html tags, normalize entities and spaces
		// based on http://www.php.net/manual/en/function.strip-tags.php#68757
		public function html2text($html){
			$text = $html;
			static $search = array(
				'@<script.+?</script>@usi',  // Strip out javascript content
				'@<style.+?</style>@usi',    // Strip style content
				'@<!--.+?-->@us',            // Strip multi-line comments including CDATA
				'@</?[a-z].*?\>@usi',         // Strip out HTML tags
			);
			$text = preg_replace($search, ' ', $text);
			// normalize common entities
			$text = self::normalizeEntities($text);
			// decode other entities
			$text = html_entity_decode($text, ENT_QUOTES, 'utf-8');
			// normalize possibly repeated newlines, tabs, spaces to spaces
			$text = preg_replace('/\s+/u', ' ', $text);
			$text = preg_replace('//u', '', $text);
			$text = preg_replace('//u', '', $text);
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
		public function normalizeEntities($text) {
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

}
?>
