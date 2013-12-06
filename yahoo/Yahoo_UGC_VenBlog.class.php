<?php
include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

Class Yahoo_UGC_VenBlog extends Yahoo_UGC {

		public $xml_mapping = "url,blog_url,page_no,content_type,language,category,region,site_name,title,body,created_time,updated_time,creator,tag,author_thumb,image";

		public function __construct() {

			$this->set_page_no(1);
			$this->set_content_type("blog");

		}

		public function set_blog_url(&$html) {

			foreach($html->find("div#banner a.url") as $element) {
				return parent::set_blog_url($element->href . "/blog");
				break;
			}

		}

		public function set_blog_title() {

			if(!empty($this->creator)) {
				return parent::set_blog_title($this->creator . "的部落格");
			} else {
				return false;
			}

		}

		public function set_title(&$html) {

			foreach($html->find("div.title a.link") as $element) {
				parent::set_title($element->plaintext);
				break;
			}
			return (!empty($this->title)) ? true : false;

		}

		public function set_body(&$html) {

			foreach($html->find("div.blog_content td.word_break") as $element) {
				parent::set_body($element->plaintext);
				break;
			}
			if(!empty($this->body)) { return true; } 
			else if(!empty($this->title)) { $this->body = $this->title; return true; } 
			else { return false; }

		}

		public function set_creator(&$html) {

			foreach($html->find("div#banner div.name") as $element) {
				parent::set_creator($element->plaintext);
				break;
			}
			return (!empty($this->creator)) ? true : false;

		}

		public function set_created_time(&$html) {

			foreach($html->find("div.blog_content") as $element) {
				$text = parent::html2text($element->plaintext);
				if(preg_match("/(\d{4})\.(\d{2})\.(\d{2}) - (\d{2}):(\d{2}) (AM|PM)+/", $text, $matchs)) {
					$date = $matchs[1] . "-" . $matchs[2] . "-" . $matchs[3] . " " . (($matchs[6] == "PM") ? ((int)$matchs[4] + 12) : $matchs[4]) . ":" . $matchs[5] . ":00";
					$D = new DateTime($date);
					parent::set_created_time($D->getTimestamp());
				}
				break;
			}
			parent::set_updated_time($this->created_time);
			return (!empty($this->created_time)) ? true : false;

		}

		public function set_tag(&$html, $limit = 4) {

			$tags = array();
			foreach($html->find("li.note-title") as $j=>$element) {
				array_push($tags, trim($element->plaintext));
				if(($j + 1) == $limit) { break; }
			}
			parent::set_tag($tags);

		}

		public function set_author_thumb(&$html) {

			include_once(dirname(__FILE__) . "/simple_html_dom.php");

			if(preg_match("/^http:\/\/verywed.com\/([0-9]+)\/blog/i", $this->blog_url, $matchs) && !empty($matchs[1])) {
				$html2 = file_get_html("http://verywed.com/vendor/key.php?key=" . $matchs[1]);
				foreach($html2->find("img[alt=" . $this->creator . "]") as $element) {
					parent::set_author_thumb($element->src);
					break;
				}
			}

		}

		public function set_image(&$html, $limit = 4) {

			$images = array();
			foreach($html->find("div.blog_content td.word_break img[src^=http://s.verywed.com/s1]") as $j=>$element) {
				array_push($images, $element->src);
				if(($j + 1) == $limit) { break; }
			}
			parent::set_image($images);

		}
}
?>
