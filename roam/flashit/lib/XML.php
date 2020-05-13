<?php

require_once('markdown.php');
require_once('markdown_exts.php');

class XMLParser {

	# Set the directory where your XML files are located here:
	public $directory = SETS_DIRECTORY;
	public $file_list;
	public $categories;
	public $sets;
	public $cards;

	function __construct() {
		if (isset($this->file_list)) { return $this->file_list; }
		$this->file_list = array();
		if (is_dir($this->directory)) {
			if ($dh = opendir($this->directory)) {
				while (($file = readdir($dh)) !== false) {
					if (pathinfo($file, PATHINFO_EXTENSION) == 'xml') {
						$this->file_list[] = $file;
					}
				}
			}
		}
		sort($this->file_list);
		return $this->file_list;
	}

	function cards_in_set($file, $set_id) {
		$this->cards = array();
		if (($xml = simplexml_load_file($this->directory . urldecode($file))) !== false) {
			foreach($xml->cards->card as $card) {
				foreach(explode(',', $card->associated_sets) as $id) {
					if ($id == $set_id) {
						$this->cards[] = array("{$card->question}", "{$card->answer}", "{$card->moreinfo}");
					}
				}
			}
		} else {
			return false;
		}
		return $this->cards;
	}

	function generate_list() {
		$this->sets = array();
		$this->categories = array();
		foreach($this->file_list as $file) {
			if (($xml = simplexml_load_file($this->directory . urldecode($file))) !== false) {
				foreach($xml->categories->category as $category) {
					$id = "{$category['id']}";
					$this->categories[$id] = array($category['name'], $category['order']);
					foreach ($category->set as $set) {
						$set_id = "{$set['id']}";
						$this->sets[$id][$set_id] = array($set['name'], $set['order'], urldecode($file));
					}
				}
			} else {
				return false;
			}
		}
		return true;
	}

	function open_study_data($cat_id, $set_id) {
		if ($this->cards_in_set($this->sets[$cat_id][$set_id][2], $set_id)) {
			foreach($this->cards as &$card) {
				foreach($card as &$element) {
					$element = str_replace('\n',"\n", $element);
					$element = str_replace("<br />","  \n", $element);
					$element = preg_replace('/\n\*\*\s/', "\n\t* ", $element);
					$element = Markdown($element);
				}
			}
			return array('title' => $this->sets[$cat_id][$set_id][0], 'questions' => $this->cards);
		} else {
			return false;
		}
	}

}

?>