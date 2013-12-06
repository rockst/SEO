<?php
include_once(dirname(__FILE__) . "/Yahoo_UGC.class.php");

Class Yahoo_UGC_Forum extends Yahoo_UGC {

		public $forum_id;
		public $xml_mapping = "url,thread_url,page_no,content_type,category,language,region,site_name,quality_type,title,body,created_time,updated_time,creator,no_of_replies,no_of_views,tag,author_thumb,image";

		public function __construct($forum_id) {

			$this->set_page_no(1);
			$this->set_content_type("forum");
			$this->set_forum_id($forum_id);

		}

		public function set_forum_id($forum_id = "") {

			$this->forum_id = intval($forum_id);

		}

		public function set_title(&$html) {

			foreach($html->find("div#post_" . $this->forum_id . " div.subject h4") as $element) {
				parent::set_title($element->plaintext);
				break;
			}
			return (!empty($this->title)) ? true : false;

		}

		public function set_body(&$html) {

			foreach($html->find("div#post_" . $this->forum_id . " div.dfs") as $element) {
				parent::set_body($element->plaintext);
				break;
			}
			if(!empty($this->body)) { return true; } 
			else if(empty($this->body)) { $this->body = $this->title; return true; } 
			else { return false; }

		}

		public function set_created_time(&$html) {

			foreach($html->find("div#post_" . $this->forum_id . " p.created") as $element) {
				if(preg_match_all("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $element->plaintext, $matches) && !empty($matches[0][0])) {
					$D = new DateTime(trim($matches[0][0]));
					parent::set_created_time($D->getTimestamp());
				}
				break;
			}
			parent::set_updated_time($this->created_time);
			return (!empty($this->created_time)) ? true : false;

		}

		public function set_creator(&$html) {

			foreach($html->find("div#post_" . $this->forum_id . " a.user_name") as $element) {
				parent::set_creator($element->plaintext);
				break;
			}

		}

		public function set_no_of_replies_views(&$html) {

			include_once(dirname(__FILE__) . "/simple_html_dom.php");

			foreach($html->find("div#post_" . $this->forum_id . " a[href^=http://verywed.com/forum/userThread/member/]") as $element) {
				$html2 = file_get_html($element->href);
				$temp = array();
				$key  = "";

				foreach($html2->find("td.subject a") as $j=>$element) {
					$temp[$j]["href"] = $element->href;
					if(preg_match("/" . $this->forum_id . "-[0-9]+.html$/", $element->href)) {
						$key = $j;
					}
				}

				foreach($html2->find("td.hitAndReplyCount") as $k=>$element) {
					$temp[$k]["hitAndReplyCount"] = $element->plaintext;
				}

				if(!empty($temp[$key]["hitAndReplyCount"])) {
					$data = explode("/", $temp[$key]["hitAndReplyCount"]);
					parent::set_no_of_replies($data[0]);
					parent::set_no_of_views($data[1]);
				}
				break;
			}

		}

		public function set_tag(&$html, $limit = 4) {

			$tags = array();
			foreach($html->find("div#post_" . $this->forum_id . " div.tag a") as $j=>$element) {
				array_push($tags, trim($element->plaintext));
				if(($j + 1) == $limit) { break; }
			}
			parent::set_tag($tags);

		}

		public function set_author_thumb(&$html) {

			foreach($html->find("div#post_" . $this->forum_id . " img.user_cover") as $element) {
				if(preg_match("/^http:\/\/s.verywed.com/i", $element->src)) {
					parent::set_author_thumb($element->src);
				}
				break;
			}

		}

		public function set_image(&$html, $limit = 4) {

			$images = array();
			foreach($html->find("div#post_" . $this->forum_id . " div.dfs img[src^=http://s.verywed.com/s1]") as $j=>$element) {
				array_push($images, $element->src);
				if(($j + 1) == $limit) { break; }
			}
			parent::set_image($images);

		}

}
?>
