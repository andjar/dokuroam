<?php
/**
 * Plugin monthcal: Display monthly calendar
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Milosz Galazka <milosz@sleeplessbeastie.eu>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once (DOKU_INC . 'inc/html.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_monthcal extends DokuWiki_Syntax_Plugin {
 
   /**
    * Get the type of syntax this plugin defines.
    *
    */
    function getType(){
        return 'substition';
    }
 
   /**
    * Define how this plugin is handled regarding paragraphs.
    *
    */
    function getPType(){
        return 'block';
    }
 
   /**
    * Where to sort in?
    *
    * Doku_Parser_Mode_html	190
    *  --> execute here <--
    * Doku_Parser_Mode_code	200
    */
    function getSort(){
        return 199;
    }
 
   /**
    * Connect lookup pattern to lexer.
    *
    */
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('{{monthcal.*?}}',$mode,'plugin_monthcal');
    }
 
   /**
    * Handler to prepare matched data for the rendering process.
    *
    */
    function handle($match, $state, $pos, Doku_Handler $handler){
	$data = array();

	// get page info
	$INFO = pageinfo();

	// default vaues
	$data['month'] = date('m');
	$data['year'] =  date('Y');
	$data['namespace'] = $INFO['namespace'];
	$data['create_links'] = 1;
	$data['week_start_on'] = 0;
	$data['display_weeks'] = 0;
	$data['do_not_create_past_links'] = 0;
	$data['borders'] = 0;
	$data['mark_today'] = 1;
	$data['align'] = 0;
	$data['create_prev_next_links'] = 0;

	$provided_data = substr($match, 11, -2);
	$arguments = explode (',', $provided_data);
	foreach ($arguments as $argument) {
		list($key, $value) = explode('=', $argument);
		switch($key) {
			case 'year':
				$data['year'] = substr($value, 0, 4);
				$data['month'] = substr($value, -2);
				break;
			case 'month':
				$data['month'] = $value;
				break;
			case 'namespace':
				$data['namespace'] = (strpos($value, ':') === false) ?  ':' . $value : $value;
				break;
			case 'create_links':
				switch(strtolower($value)) {
					case 'no':
						$data['create_links'] = 0;
						break;
					case 'local':
						$data['create_links'] = 2;
						break;
					default:
						$data['create_links'] = 1;
						break;
				}
				break;
			case 'week_start_on':
				if (strtolower($value) == "sunday")
					$data['week_start_on'] = 1;
				else
					$data['week_start_on'] = 0;
				break;
			case 'display_weeks':
				switch(strtolower($value)) {
					case 'no':
						$data['display_weeks'] = 0;
						break;
					default:
						$data['display_weeks'] = 1;
						break;
				}
				break;
			case 'do_not_create_past_links':
				switch(strtolower($value)) {
					case 'no':
						$data['do_not_create_past_links'] = 0;
						break;
					default:
						$data['do_not_create_past_links'] = 1;
						break;
				}
				break;
			case 'create_prev_next_links':
				switch(strtolower($value)) {
					case 'no':
						$data['create_prev_next_links'] = 0;
						break;
					default:
						$data['create_prev_next_links'] = 1;
						break;
				}
				break;
			case 'borders':
				switch(strtolower($value)) {
					case 'all':
						$data['borders'] = 1;
						break;
					case 'table':
						$data['borders'] = 2;
						break;
					default:
						$data['borders'] = 0;
						break;
				}
				break;
			case 'mark_today':
				switch(strtolower($value)) {
					case 'no':
						$data['mark_today'] = 0;
						break;
					default:
						$data['mark_today'] = 1;
						break;
				}
				break;
			case 'align':
				switch(strtolower($value)) {
					case 'left':
						$data['align'] = 1;
						break;
					case 'right':
						$data['align'] = 2;
						break;
					default:
						$data['align'] = 0;
						break;
				}
				break;
		}
	}
        return $data;
    }
 
   /**
    * Handle the actual output creation.
    *
    */
    function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode == 'xhtml'){
            $renderer->doc .= $this->create_calendar($data);
            return true;
        }
        return false;
    }

   /**
    * Create calendar
    *
    */
    function create_calendar($data) {
	// date today
	$date_today = new DateTime();

	// date yesterday
	$date_yesterday = (new DateTime($date_today->format('Y-m-d')))->modify('-1 day');


	// date: from -> to
	$date_from = new DateTime($data['year'] . "-" . $data['month'] . "-01");
	$date_to   = (new DateTime($date_from->format('Y-m-d')))->modify('+1 month');

	// date prev/next month
	$date_prev_month = (new DateTime($date_from->format('Y-m-d')))->modify('-1 month');
	$date_next_month = $date_to; //(new DateTime($date_to->format('Y-m-d')))->modify('+1 month');

	$date_interval = new DateInterval('P1D');
	$date_range    = new DatePeriod($date_from, $date_interval, $date_to);

	// first day in on ...
	$date_from_on_weekday = $date_from->format('N');

	// language specific
	$weekdays = $this->getLang('monthcal_weekdays_short');
	$months   = $this->getLang('monthcal_months');

	// weekday variable which is used inside each loop
	$wday = 1;

	// move by one element to the right if week starts at Sunday
	if ($data['week_start_on'] == 1) {
		if ($date_from_on_weekday <= 6)
			$date_from_on_weekday += 1;
		else
			$date_from_on_weekday  = 1;
	}

	// border css
	switch($data['align']) {
		case 1:
			$css_align = 'left';
			break;
		case 2:
			$css_align = 'right';
			break;
		default:
			$css_align = '';
			break;
	}

	// border css
	switch($data['borders']) {
		case 1:
			$css_table_border = 'withborder';
			$css_td_border    = 'withborder';
			break;
		case 2:
			$css_table_border = 'withborder';
			$css_td_border    = 'borderless';
			break;
		case 0:
			$css_table_border = 'borderless';
			$css_td_border    = 'borderless';
			break;
	}

	// html code
	$html = '<table class="monthcal ' . $css_table_border . ' ' . $css_align . '">';

	// colspan for month/year
	$colspan_month = 4;
	if ($data['display_weeks'] == '1') {
		$colspan_year= 4;
	} else {
		$colspan_year= 3;
	}

	// header
	$html .= '<tr class="description">';
	$html .= '<td class="month ' . $css_td_border . '" colspan="' . $colspan_month . '">' . $months[$date_from->format('m')-1] . ' ';
	if ($data['create_prev_next_links']){
		$html .= html_wikilink($data['namespace'] . ':' . $date_prev_month->format('Y') . $date_prev_month->format('m') . ':', '<<');
		$html .= html_wikilink($data['namespace'] . ':' . $date_next_month->format('Y') . $date_next_month->format('m') . ':', '>>');
	}
	$html .= '</td>';
	$html .= '<td class="year" colspan="' . $colspan_year . '">' . $date_from->format('Y') . '</td></tr>';

	// swap weekdays if week starts at Sunday
	if ($data['week_start_on'] == 1) { $weekdays=array($weekdays[6],$weekdays[0],$weekdays[1],$weekdays[2],$weekdays[3],$weekdays[4],$weekdays[5]);}

	// append empty header for week numbers
	if ($data['display_weeks'] == '1') {
		array_unshift($weekdays,"");
	}

	// weekdays
	$html .= '<tr>';
	foreach($weekdays as $weekday) {
		$html .= '<th class="' . $css_td_border . '">' . $weekday . '</th>';
	}
	$html .= '</tr>';
	$html .= '<tr>';

	// initial week number
	if ($data['display_weeks'] == '1') {
		$html .= '<td class="' . $css_td_border . '">' . $date_from->format("W") . '</td>';
	}

	// first empty days
	if ($date_from_on_weekday > 1) {
		for($wday;$wday < $date_from_on_weekday;$wday++) {
			$html .= '<td class="' . $css_td_border . '"></td>';
		}
	}

	// month days
	foreach($date_range as $date) {
		if ($wday > 7) {
			$wday = 1;
			$html .= "</tr>";
			$html .= "<tr>";

			if ($data['display_weeks'] == '1') {
				$html .= '<td class="' . $css_td_border . '">' . $date->format("W") . '</td>';
			}
		}

		if ($date->format('Ymd') == $date_today->format('Ymd') and $data['mark_today'] == 1)
			$css_today='today';
		else
			$css_today='';

		if ($data['create_links'] == '1' ) {
			$id = $data['namespace'] . ':' . $date->format('Y') . $date->format('m') . $date->format('d');
			$linkstring = '&newpagetemplate=journal:tmplt&newpagevars=@tododate@%2C'.$date->format('Y') . '-' . $date->format('m') . '-' . $date->format('d');
			if (($data['do_not_create_past_links'] == '1') and ($date->format('Ymd') <  $date_today->format('Ymd'))) {
				$page_exists = null;
				resolve_pageid($data['namespace'] . ':' . $date->format('Y') . ':' . $date->format('m'), $date->format('d'), $page_exists);
				if ($page_exists) {
					$html_day = html_wikilink($id, $date->format('d'));
				} else {
					$html_day = $date->format('d');
				}
			} else {
				//$html_day = html_wikilink($id, $date->format('d'));
				//$html_day = wl($linkstring, $date->format('d'));
				$html_day = '<a href="' . wl($id) . $linkstring . '">' . $date->format('d') . '</a>';
			}
		} else if ($data['create_links'] == '2' ) {
			$html_day = '<a href="#section' . $date->format('d') . '">' . $date->format('d') . '</a>';
		} else {
			$html_day = $date->format('d');
		}

		$html .= '<td class="' . $css_td_border . ' ' . $css_today . '">' . $html_day .  '</td>';
		$wday++;
	}

	// last empty days
	if ($wday < 8) {
		for($wday;$wday<8;$wday++) {
			$html .= '<td class="' . $css_td_border . '"></td>';
		}
	}

	// close table
	$html .= '</table>';

	// return table
        return $html;
    }
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
