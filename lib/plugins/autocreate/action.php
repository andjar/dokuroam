<?php
/**
 * DokuWiki Plugin autocreate (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Anders H. Jarmund <ajarmund@gmail.com>
 *
 * Based on
 * 1. DokuWiki Plugin autostartpage (Action Component)
 *      @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 *      @author  Charles Knight <charles@rabidaudio.com>
 * 2. OrphansWanted Plugin: Display Orphans, Wanteds and Valid link information
 *      @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *      @author     <dae@douglasedmunds.com>
 *      @author     Andy Webber <dokuwiki at andywebber dot com>
 *      @author     Federico Ariel Castagnini
 *      @author     Cyrille37 <cyrille37@gmail.com>
 *      @author     Rik Blok <rik dot blok at ubc dot ca>
 *      @author     Christian Paul <christian at chrpaul dot de>
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_autocreate extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler &$controller) {
       $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this, 'autocreate_handle');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @author Charles Knight, charles@rabidaudio.com
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function autocreate_handle(Doku_Event &$event, $param) {
        global $conf;
        global $INFO;
        $data = $event->data;
        $id=$data[1].":".$data[2];
        $file=wikiFN($id);
        
        global $conf;

        if (!defined('LINK_PATTERN')) define('LINK_PATTERN', '%\[\[([^\]|#]*)(#[^\]|]*)?\|?([^\]]*)]]%');

        if(!preg_match("/.*\.txt$/", $file)) {
            return;
        }

        $currentID = pathID($file);
        $currentNS = getNS($currentID);

        if($conf['allowdebug']) echo sprintf("<p><b>%s</b>: %s</p>\n", $file, $currentID);

        // echo "  <!-- checking file: $file -->\n";
        $body = io_readFile($file);
        
        // ignores entries in blocks that ignore links
        foreach( array(
                  '@<nowiki>.*?<\/nowiki>@su',
                  '@%%.*?%%@su',
                  '@<php>.*?</php>@su',
                  '@<PHP>.*?</PHP>@su',
                  '@<html>.*?</html>@su',
                  '@<HTML>.*?</HTML>@su',
                  '@^( {2,}|\t)[^\*\- ].*?$@mu',
                  '@<code[^>]*?>.*?<\/code>@su',
                  '@<file[^>]*?>.*?<\/file>@su'
        )
        as $ignored )
        {
            $body = preg_replace($ignored, '',  $body);
        }

        $links = array();
        preg_match_all( LINK_PATTERN, $body, $links );
        $cc = 1;
        foreach($links[1] as $link) {
            if($conf['allowdebug']) echo sprintf("--- Checking %s<br />\n", $link);

            if( (0 < strlen(ltrim($link)))
            and ! preg_match('/^[a-zA-Z0-9\.]+>{1}.*$/u',$link) // Interwiki
            and ! preg_match('/^\\\\\\\\[\w.:?\-;,]+?\\\\/u',$link) // Windows Share
            and ! preg_match('#^([a-z0-9\-\.+]+?)://#i',$link) // external link (accepts all protocols)
            and ! preg_match('<'.PREG_PATTERN_VALID_EMAIL.'>',$link) // E-Mail (pattern above is defined in inc/mail.php)
            and ! preg_match('!^#.+!',$link) // inside page link (html anchor)
            ) {
                # remove parameters
                $link = preg_replace('/\?.*/', '', $link);

                $pageExists = false;
                resolve_pageid($data[1], $link, $pageExists );
                resolve_pageid($currentNS, $link, $pageExists );
                if ($conf['allowdebug']) echo sprintf("---- link='%s' %s ", $link, $pageExists?'EXISTS':'MISS');

                if(((strlen(ltrim($link)) > 0)           // there IS an id?
                and !auth_quickaclcheck($link) < AUTH_READ)) {
                    // should be visible to user
                    //echo "      <!-- adding $link -->\n";

                    if($conf['allowdebug']) echo ' A_LINK' ;

                    $link= utf8_strtolower( $link );
                }
                else
                {
                    if($conf['allowdebug']) echo ' EMPTY_OR_FORBIDDEN' ;
                }
            } // link is not empty and is a local link?
            else {
                if($conf['allowdebug']) echo ' NOT_INTERNAL';
            }

            if($conf['allowdebug']) echo "<br />\n";
            
            if($pageExists == FALSE){ 
                
                $id = $link;
                $ns = getNS($id);
                $page = noNS($id);
                
                // Here we create the new pages
                $templatefile = wikiFN( $ns . ':' . $this->getConf('templatefile'), '', false);
                if(@file_exists($templatefile)){
                    $wikitext=io_readFile($templatefile);
                }

                $silent=$this->getConf('silent');
                $ns_sepchar = ":";

                $parent=implode($ns_sepchar, array_splice(preg_split("/".preg_quote($ns_sepchar, "/")."/", $ns), 0, -1));
                $goodns=preg_replace("/".$conf['sepchar']."/"," ",noNS($ns));
                $page=preg_replace("/".$conf['sepchar']."/"," ",noNS($id));
                $f=$conf['start'];

                /**THESE ARE THE CODES FOR TEMPLATES**/
                // @ID@         full ID of the page
                // @NS@         namespace of the page
                // @PAGE@       page name (ID without namespace and underscores replaced by spaces)
                // @!PAGE@      same as above but with the first character uppercased
                // @!!PAGE@     same as above but with the first character of all words uppercased
                // @!PAGE!@     same as above but with all characters uppercased
                // @FILE@       page name (ID without namespace, underscores kept as is)
                // @!FILE@      same as above but with the first character uppercased
                // @!FILE!@     same as above but with all characters uppercased
                // @USER@       ID of user who is creating the page
                // @NAME@       name of user who is creating the page
                // @MAIL@       mail address of user who is creating the page
                // @DATE@       date and time when edit session started
                /**PLUS WE ADDED THESE**/
                // @!NS@        namespace of the page (with spaces) but with the first character uppercased
                // @!!NS@       namespace of the page (with spaces) but with the first character of all words uppercased
                // @!!NS!@      namespace of the page (with spaces) but with all characters uppercased
                // @PARENT@     the name of the parent namespace. Blank if parent is top
                // @DATE=STRFTIME@   Where `STRFTIME` is a strftime configure string of page creation time,
                //       e.g. %a %d-%m-%y => Thu 06-12-12

                $wikitext=preg_replace("/@NS@/", $ns, $wikitext);
                $wikitext=preg_replace("/@!NS@/", ucfirst($goodns), $wikitext);
                $wikitext=preg_replace("/@!!NS@/", ucwords($goodns), $wikitext);
                $wikitext=preg_replace("/@!!NS!@/", strtoupper($goodns), $wikitext);
                $wikitext=preg_replace("/@ID@/", $id, $wikitext);
                $wikitext=preg_replace("/@PAGE@/",$page, $wikitext);
                $wikitext=preg_replace("/@!PAGE@/",ucfirst($page), $wikitext);
                $wikitext=preg_replace("/@!!PAGE@/",$uupage=ucwords($page), $wikitext);
                $wikitext=preg_replace("/@!PAGE!@/",strtoupper($page), $wikitext);
                $wikitext=preg_replace("/@FILE@/",$f, $wikitext);
                $wikitext=preg_replace("/@!FILE@/",ucfirst($f), $wikitext);
                $wikitext=preg_replace("/@!FILE!@/",strtoupper($f), $wikitext);
                $wikitext=preg_replace("/@USER@/",$_SERVER['REMOTE_USER'], $wikitext);
                $wikitext=preg_replace("/@NAME@/",$INFO['userinfo']['name'], $wikitext);
                $wikitext=preg_replace("/@MAIL@/",$INFO['userinfo']['mail'], $wikitext);
                $wikitext=preg_replace("/@DATE@/",strftime("%D"), $wikitext);
                $wikitext=preg_replace("/@PARENT@/",$parent, $wikitext);
                if(preg_match("/@DATE=(.*)@/", $wikitext, $matches)){
                    $wikitext=str_replace($matches[0], strftime($matches[1]), $wikitext);
                }

                saveWikiText($link, $wikitext, "autocreate", $minor = false); 
                }
            
        }
    }
    
}
