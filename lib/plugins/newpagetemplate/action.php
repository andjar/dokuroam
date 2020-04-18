<?php
/*
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jason Grout <jason-doku@creativetrax.com>>
 * 
 * Modifications by Sergio (1 Apr 2007), an unidentified author, 
 * and  Niko Paltzer (15 Jan 2010).
 *
 *  brought up-to-date with current Dokuwiki Event changes
 *  and event handling by Myron Turner (April 7 2011);
 *  new security features (September 2 2011)
 *  turnermm02@shaw.ca     
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
require_once(DOKU_INC.'inc/init.php');
 
class action_plugin_newpagetemplate extends DokuWiki_Action_Plugin {
   var $done = false;
   var $allow = true;
  /**
   * return some info
   */
  function getInfo(){
    return array(
      'author' => 'Jason Grout, Myron Turner',
      'email'  => 'jason-doku@creativetrax.com',
      'date'   => '2007-02-24',
      'name'   => 'newpagetemplate',
      'desc'   => 'Loads into the new page creation box a template specified in the $_REQUEST "newpagetemplate" parameter (i.e., can be passed in the URL or as a form value).',
      'url'    => '',
    );
  }
 
  /**
   * register the eventhandlers
   *  Modified by 
   *  @author Myron Turner
   *  turnermm02@shaw.ca     
   */
  function register(Doku_Event_Handler $contr){

    $contr->register_hook('COMMON_PAGE_FROMTEMPLATE', 'BEFORE', $this, 'pagefromtemplate', array());
    $contr->register_hook('COMMON_PAGETPL_LOAD', 'BEFORE', $this, 'pagefromtemplate', array());
	$contr->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'check_acl', array());
	$contr->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'write_msg', array());
	$contr->register_hook('HTML_PAGE_FROMTEMPLATE', 'BEFORE', $this, 'pagefromtemplate', array());
  }

  /**
   *  pagefromtemplate
   *  Modified by 
   *  @author Myron Turner
   *  turnermm02@shaw.ca     
   */
 
  function pagefromtemplate(Doku_Event $event, $param) {  
    if($this->done) return;
    $this->done=true;
    
    if(strlen(trim($_REQUEST['newpagetemplate']))>0) {
	if(!$this->allow) {	 
	   return ;
	}
      global $conf;
      global $INFO;
      global $ID;
	
      $tpl = io_readFile(wikiFN($_REQUEST['newpagetemplate']));
 
      if($this->getConf('userreplace')) {
        $stringvars =
             array_map(create_function('$v', 'return explode(",",$v,2);'),
                 explode(';',$_REQUEST['newpagevars']));
        foreach($stringvars as $value) {
             $tpl = str_replace(trim($value[0]),hsc(trim($value[1])),$tpl);
	    }
     }
 
      if($this->getConf('standardreplace')) {
        // replace placeholders
        $file = noNS($ID);       
        $page = cleanID($file) ;
        if($this->getConf('prettytitles')) {        
            $title= str_replace('_',' ',$page);
        }
       else {
           $title = $page;
       }
        $tpl = str_replace(array(
                              '@ID@',
                              '@NS@',
                              '@FILE@',
                              '@!FILE@',
                              '@!FILE!@',
                              '@PAGE@',
                              '@!PAGE@',
                              '@!!PAGE@',
                              '@!PAGE!@',
                              '@USER@',
                              '@NAME@',
                              '@MAIL@',
                              '@DATE@',
                              '@EVENT@'
                           ),
                           array(
                              $ID,
                              getNS($ID),
                              $file,
                              utf8_ucfirst($file),
                              utf8_strtoupper($file),
                              $page,
                              utf8_ucfirst($title),
                              utf8_ucwords($title),
                              utf8_strtoupper($title),                              
                              $_SERVER['REMOTE_USER'],
                              $INFO['userinfo']['name'],
                              $INFO['userinfo']['mail'],
                              $conf['dformat'],
                              $event->name ,
                           ), $tpl);
 
        // we need the callback to work around strftime's char limit
        $tpl = preg_replace_callback('/%./',create_function('$m','return strftime($m[0]);'),$tpl);
      }
      if($this->getConf('skip_unset_macros')) {
          $tpl = preg_replace("/@.*?@/ms","",$tpl);
      }
	  if($event->name == 'HTML_PAGE_FROMTEMPLATE') {
	     $event->result=$tpl;
	  }
	  else { 
         $event->data['tpl'] = $tpl;
      }
      $event->preventDefault(); 
    }
  }

  public function check_acl(Doku_Event $event,$param) {
      global $INPUT;
      if (!$INPUT->has('newpagetemplate')) {
          return;
      }

      $pq = trim($INPUT->str('newpagetemplate'), ':');
      if (auth_quickaclcheck($pq) < AUTH_CREATE) {
          $this->allow = false;
      }
   }
   
  function write_msg (&$event,$param) {
    if($this->allow) return; 
    global $ID,$INPUT;
    
    echo"<h1> Permission Denied </h1>";
    echo "You do not have access to the template  " . htmlentities($INPUT->str('newpagetemplate')) . '</br>';	 
	unlock($ID); 
	$event->preventDefault(); 
  }
}
