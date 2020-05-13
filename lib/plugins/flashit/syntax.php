<?php
/**
 * Plugin Iframe: Inserts an iframe element to include the specified url
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
 // must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_iframe extends DokuWiki_Syntax_Plugin {

    function getType() { return 'substition'; }
    function getSort() { return 305; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('{{url>.*?}}',$mode,'plugin_iframe'); }

    function handle($match, $state, $pos, Doku_Handler $handler){
        $match = substr($match, 6, -2);
        list($url, $alt)   = explode('|',$match,2);
        list($url, $param) = explode(' ',$url,2);

        // javascript pseudo uris allowed?
        if (!$this->getConf('js_ok') && substr($url,0,11) == 'javascript:'){
            $url = false;
        }

        // set defaults
        $opts = array(
                    'url'    => $url,
                    'width'  => '98%',
                    'height' => '400px',
                    'alt'    => $alt,
                    'scroll' => true,
                    'border' => true,
                    'align'  => false,
                );

        // handle size parameters
        $matches=array();
        if(preg_match('/\[?(\d+(em|%|pt|px)?)\s*([,xX]\s*(\d+(em|%|pt|px)?))?\]?/',$param,$matches)){
            if($matches[4]){
                // width and height was given
                $opts['width'] = $matches[1];
                if(!$matches[2]) $opts['width'] .= 'px'; //default to pixel when no unit was set
                $opts['height'] = $matches[4];
                if(!$matches[5]) $opts['height'] .= 'px'; //default to pixel when no unit was set
            }elseif($matches[2]){
                // only height was given
                $opts['height'] = $matches[1];
                if(!$matches[2]) $opts['height'] .= 'px'; //default to pixel when no unit was set
            }
        }

        // handle other parameters
        if(preg_match('/noscroll(bars?|ing)?/',$param)){
            $opts['scroll'] = false;
        }
        if(preg_match('/no(frame)?border/',$param)){
            $opts['border'] = false;
        }
        if(preg_match('/(left|right)/',$param,$matches)){
            $opts['align'] = $matches[1];
        }

        return $opts;
    }

    function render($mode, Doku_Renderer $R, $data) {
        if($mode != 'xhtml') return false;
        
        if(!plugin_isdisabled('tag')) {
            $tag =& plugin_load('helper', 'tag');
            if($tag) {
                $taggedPages = $tag->getTopic('notes', NULL, 'flashit');
            }
        }
        
        $this->make_card_page($taggedPages);

        $R->doc .= '<iframe src="roam/flashit/index.php" height="600px" width="100%" style="border:none;"></iframe>';
        
        return true;
    }
    
    function make_card_page($taggedPages){
        $out_string = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $out_string .= '<flashcards>' . PHP_EOL;
        $out_string .= '<categories>' . PHP_EOL;
        $out_string .= '<category name="All questions" order="1" id="1">' . PHP_EOL;
        $out_string .= '<set name="Questions" id="1"/>' . PHP_EOL;
        $out_string .= '</category>' . PHP_EOL;
        $out_string .= '</categories>' . PHP_EOL;
        $out_string .= '<cards>' . PHP_EOL;
        $num = 1;
        foreach($taggedPages as $page){
            $out_string .= $this->make_card($page['id'], $num) . PHP_EOL;
        }
        $out_string .= '</cards>' . PHP_EOL;
        $out_string .= '</flashcards>';
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/roam/flashit/sets/set.xml', $out_string);
        return true;
    }
    
    function make_card($page, &$num){
        $page_content = file_get_contents(wikiFN($page));
        $parts = explode("\n", $page_content);
        $parts = new SplFileObject(wikiFN($page));
        
        $out_string = '';
        $insideWRAP = FALSE;
        $insideQuestion = FALSE;
        $insideAnswer = FALSE;
        $insideMoreInfo = FALSE;
        foreach ($parts as $line) {
            if($insideWRAP == TRUE){
                if(strpos($line, '</WRAP>') !== false){
                    if($insideQuestion == FALSE && $insideAnswer == FALSE && $insideMoreInfo == FALSE){
                        $insideWRAP = FALSE;
                        $out_string .= $this->make_card_helper($num, $question, $answer, $moreinfo);
                        $num++;
                    }
                    if($insideQuestion){
                        $insideQuestion = FALSE;
                    }
                    if($insideAnswer){
                        $insideAnswer = FALSE;
                    }
                    if($insideMoreInfo){
                        $insideMoreInfo = FALSE;
                    }
                }else{
                    if($insideQuestion !== FALSE){
                        $question .= $line;
                    }
                    if($insideAnswer !== FALSE){
                        $answer .= $line;
                    }
                    if($insideMoreInfo !== FALSE){
                        $moreinfo .= $line;
                    }
                    if(strpos($line, '<WRAP question>') !== false){
                        $insideQuestion = TRUE;
                    }
                    if(strpos($line, '<WRAP answer>') !== false){
                        $insideAnswer = TRUE;
                    }
                    if(strpos($line, '<WRAP moreinfo>') !== false){
                        $insideMoreInfo = TRUE;
                    }
                }
            }
            if(strpos($line, '<WRAP flashit>') !== false){
                $insideWRAP = TRUE;
                $question = '';
                $answer = '';
                $moreinfo = '';
            }
            
        }
        
        return $out_string;
    }
    
    function make_card_helper($num, $question, $answer, $moreinfo){
        $out_string = '<card id="' . $num . '">' . PHP_EOL;
        $out_string .= '<question>' . $question . '</question>' . PHP_EOL;
        $out_string .= '<answer>' . $answer . '</answer>' . PHP_EOL;
        if(strlen($moreinfo) > 1){
            $out_string .= '<moreinfo>' . $moreinfo .'</moreinfo>' . PHP_EOL;
        }
        $out_string .= '<associated_sets>1</associated_sets>' . PHP_EOL;
        $out_string .= '</card>' . PHP_EOL;
        return $out_string;
    }
    
}
