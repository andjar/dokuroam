<?php
/**
 * Alternate span syntax component for the wrap plugin
 *
 * Defines  <inline> ... </inline> syntax
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Anika Henke <anika@selfthinker.org>
 */

require_once(dirname(__FILE__).'/span.php');

class syntax_plugin_wrap_spaninline extends syntax_plugin_wrap_span {

    protected $special_pattern = '<inline\b[^>\r\n]*?/>';
    protected $entry_pattern   = '<inline\b.*?>(?=.*?</inline>)';
    protected $exit_pattern    = '</inline>';

}

