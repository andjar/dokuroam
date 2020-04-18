<?php
/**
 * Alternate div syntax component for the wrap plugin
 *
 * Defines  <WRAP> ... </WRAP> syntax
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Anika Henke <anika@selfthinker.org>
 */

require_once(dirname(__FILE__).'/div.php');

class syntax_plugin_wrap_divwrap extends syntax_plugin_wrap_div {

    protected $special_pattern = '<WRAP\b[^>\r\n]*?/>';
    protected $entry_pattern   = '<WRAP\b.*?>(?=.*?</WRAP>)';
    protected $exit_pattern    = '</WRAP>';

}

