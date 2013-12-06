<?php
include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

Class Yahoo_UGC_Blog extends Yahoo_UGC {

		public $xml_mapping = "url,blog_url,page_no,content_type,language,category,region,site_name,title,body,created_time,updated_time,creator,tag,author_thumb,image";

		public function __construct() {

			$this->set_page_no(1);
			$this->set_content_type("blog");

		}

		public function set_blog_url() {

			return parent::set_blog_url("http://verywed.com/vwblog/" . preg_replace("/^http:\/\/verywed.com\/vwblog\/(.*)\/article\/\d+.*/i", "\$1", $this->url) . "/");

		}

		public function set_title(&$html) {

			foreach($html->find("li.article-title a") as $element) {
				parent::set_title($element->plaintext);
				break;
			}
			return (!empty($this->title)) ? true : false;

		}

		public function set_body(&$html) {

			foreach($html->find("div.article-body") as $element) {
				parent::set_body($element->plaintext);
				break;
			}
			return (!empty($this->body)) ? true : false;

		}

		public function set_blog_title(&$html) {

			foreach($html->find("div#blogsub h2,div#blogtitle h1,span.blogtitle a") as $element) {
				parent::set_blog_title($element->plaintext);
				break;
			}
			return (!empty($this->blog_title)) ? true : false;

		}

		public function set_created_time(&$html) {

			foreach($html->find("li.article-date") as $element) {
				$text = parent::html2text($element->plaintext);
				$date = preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) \d{2}:\d{2} (AM|PM)+/", "\$1-\$2-\$3", $text);
				$type = preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$6", $text);
				if($type == "PM") {
					$time = (int) preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$4", $text) + 12;
					$time.= ":" . preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$5:00", $text);
				} else {
					$time = preg_replace("/^(\d{4}) \/ (\d{2}) \/ (\d{2}) (\d{2}):(\d{2}) (AM|PM)+/", "\$4:\$5:00", $text);
				}
				$D = new DateTime($date . " " . $time);
				parent::set_created_time($D->getTimestamp());
				break;
			}
			parent::set_updated_time($this->created_time);
			return (!empty($this->created_time)) ? true : false;

		}

		public function set_creator(&$html) {

			parent::set_creator(preg_replace("/^http:\/\/verywed.com\/vwblog\/(.*)\/article\/\d+.*/i", "\$1", $this->url));

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

			foreach($html->find("div.avatar a img") as $element) {
				if(preg_match("/^http:\/\/s.verywed.com/i", $element->src)) {
					parent::set_author_thumb($element->src);
				}
				break;
			}

		}

		public function set_image(&$html, $limit = 4) {

			$images = array();
			foreach($html->find("div.article-body img[src^=http://s.verywed.com/s1]") as $j=>$element) {
				array_push($images, $element->src);
				if(($j + 1) == $limit) { break; }
			}
			parent::set_image($images);

		}

}
?>
