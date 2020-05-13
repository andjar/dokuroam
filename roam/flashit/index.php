<?php
#
# Total Recall - the flash card webapp.
# Stand-alone PHP script for Flash Cards with a Javascript interface and logic.
# By: Dr. Brady Bouchard
# brady@thewellinspired.com
# Available at: https://github.com/bouchard/totalrecall.drbouchard.ca
#
# ------------------------------------------------------------------
#
# Copyright Dr. Brady Bouchard 2016.
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ------------------------------------------------------------------
#
# See the README (README.markdown) for more information.

error_reporting(E_ALL);

# Configuration (is editing allowed?)
require_once('config.php');
# The XML Parser.
require_once('lib/XML.php');
# Allow PHP to choose the line break depending on the operating system.
ini_set('auto_detect_line_endings', true);
# Safari chokes on unicode characters unless this is here.
header("Content-type:text/html; charset=utf-8");

class Navigation {

	public $action;			# The current user action.
	public $page_title;
	public $study_data;
	public $xml;			# A handle to an instance of the CSV class.
	public $start_index;	# Which card do we want to start with?
	public $cat_id;
	public $set_id;

	function __construct() {
		$this->xml = new XMLParser;
		$this->xml->generate_list();
		$this->action = (strlen($_SERVER['QUERY_STRING']) > 0 ? 'study' : 'choose');
		$qs = preg_split('/&/', $_SERVER['QUERY_STRING']);
		$this->start_index = (isset($qs[2]) && strlen($qs[2]) > 0 ? $qs[2] : null);
		$this->set_id = (isset($qs[1]) && strlen($qs[1]) > 0 ? $qs[1] : null);
		$this->cat_id = (isset($qs[0]) && strlen($qs[0]) > 0 ? $qs[0] : null);
		switch ($this->action) {
			case 'study':
				$this->study();
				break;
			case 'choose':
				$this->choose_directory();
				break;
			default:
				$this->choose_directory();
		}
	}

	function study() {
		if ($this->study_data = $this->xml->open_study_data($this->cat_id, $this->set_id)) {
			$this->page_title = 'Studying: ' . $this->study_data['title'];
		} else {
			$this->error = 'Invalid study set.';
			$this->action = 'choose';
			$this->page_title = 'Choose a Study Set';
		}
	}

	function choose_directory() {
		$this->page_title = "Let's get this study party started...";
	}
}

$nav = new Navigation;

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width = 660">
<meta name="author" content="Dr. Brady Bouchard; https://github.com/bouchard/totalrecall.drbouchard.ca">
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=8">
<title>Total Recall - <?php echo $nav->page_title; ?></title>
<link href="css/base.css" rel="stylesheet" type="text/css">
<link href="css/study.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">
<link href="js/fancybox/jquery.fancybox-1.3.1.css" rel="stylesheet" type="text/css">
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery.jstore.js" type="text/javascript"></script>
<script src="js/study.js" type="text/javascript"></script>
<script src="js/fancybox/jquery.fancybox-1.3.1.pack.js" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[
<?php if ($nav->action == 'choose') : ?>
	$(document).ready(function() {
		$('#set-list ul li a').each(function() {
			if ($.getItem($(this).attr('rel') + '_date') != null) {
				today = new Date();
				last_tested = new Date(Date.parse($.getItem($(this).attr('rel') + '_date')));
				if (today.toDateString() == last_tested.toDateString()) {
					if ($.getItem($(this).attr('rel') + '_card_counts') != null) {
						studied_count = $.getItem($(this).attr('rel') + '_card_counts')[0];
						total_count = $.getItem($(this).attr('rel') + '_card_counts')[1];
						percentage = Math.round(100 - (100 * studied_count / total_count));
						$(this).html('<strong>' + $(this).html() + '</strong> &nbsp;' +
						  percentage + '%' + ' (' +
						  studied_count + ' of ' + total_count + ' left today)'
						);
					}
				}
			}
		});
	});
<?php endif; ?>
<?php if ($nav->action == 'study') : ?>
<?php
if (count($nav->study_data['questions']) > 0) {
	echo "var \$fc = " . json_encode($nav->study_data['questions']) . ";\n";
	echo "var \$cat_id = " . json_encode($nav->cat_id) . ";\n";
	echo "var \$set_id = " . json_encode($nav->set_id) . ";\n";
	if (isset($nav->start_index)) {
		echo "var \$start_index = " . json_encode($nav->start_index) . ";\n";
	}
}
?>
<?php endif; ?>
//]]>
</script>

</head>

<body>

<div id="nav-buttons">
	<div id="reset-database" style="display: none;">
		<a href="#">reset progress</a>
	</div>
</div>
<?php

	if ($nav->action == 'choose') {
		echo "<h1>$nav->page_title</h1>\n";
		echo "<div id=\"set-list\">\n";
		if (count($nav->xml->categories) > 0) {
			foreach($nav->xml->categories as $cat_id => $cat_data) {
				echo "<h2>" . $cat_data[0] . "</h2>\n";
				if (count($nav->xml->sets[$cat_id]) > 0) {
					echo "<ul>\n";
					foreach ($nav->xml->sets[$cat_id] as $set_id => $set_data) {
						echo "<li><a href=\"?" . urlencode($cat_id) . "&" . urlencode($set_id) . "\" rel=\"" . urlencode($cat_id) . "_" . urlencode($set_id) . "\">" . $set_data[0] . "</a></li>\n";
					}
					echo "</ul>\n";
				} else {
					echo "You don't have any study sets in this category.\n";
				}
			}
		} else {
			echo 'You don\'t have any categories available in the directory ' . $nav->xml->directory . "\n";
		}
		echo "</div>\n";
	} elseif ($nav->action == 'study') {
?>
<div id="progress-bar" style="display: none;">
</div>

<div id="stop-it">
	<a href="./" id="stop-it-link">stop studying</a>
</div>

<div id="more-info" style="display: none;">
	<a id="show-more-info" href="#">more information about this topic (space)</a>
</div>

<div id="more-info-box" style="display: none;">
	<div id="more-info-content">
		more information...
	</div>
</div>

<div id="question-box">
	<div id="question-content">
		sorry, but you need to enable javascript for this to work.
	</div>
	<div id="question-controls">
		<form>
			<button id="show-answer">click here or press (space) to show answer</button>
		</form>
	</div>
</div>

<div id="answer-box">
	<div id="answer-content">
		loading answer...
	</div>
	<div id="answer-controls">
		<form action="#">
			<fieldset>
				<button class="b1 sbad scorebutton" id="1">no clue ( J )</button>
				<button class="b2 sbad scorebutton" id="2">poor ( K )</button>
				<button class="b3 scorebutton sgood" id="3">fair ( L )</button>
				<button class="b5 scorebutton sgood" id="5">perfect ( ; )</button>
			</fieldset>
		</form>
	</div>
</div>

<div id="debug" style="font-size: 1.5em;">
</div>

<?php } ?>

<?php if ($nav->action == 'choose') : ?>
<div id="footer">
<p><a href="http://github.com/brady8/total-recall">Total Recall</a>, developed by <a href="mailto:brady@thewellinspired.com">Dr. Brady Bouchard</a>.</p>
<p>Requires <strong>a modern browser</strong>.</p>
<p>Using the SM-2 algorithm for <a href="http://en.wikipedia.org/wiki/Spaced_repetition">spaced interval learning</a>.<br>The frequency with which cards are shown is based on how you do on previous attempts.</p>
<?php if (ADD_SUBMIT_LINK || $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == 'totalrecall.drbouchard.ca') { ?>
<p>If you have flash cards to contribute or corrections to those already here, please feel free to <a href="mailto:<?php echo (CONTRIB_EMAIL_LINK); ?>">email</a> me!</p>
<?php } ?>
</div>
<?php endif; ?>

</body>
</html>
