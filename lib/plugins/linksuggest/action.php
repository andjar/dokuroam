<?php
/**
* DokuWiki Plugin linksuggest (Action Component)
*
* ajax autosuggest for links
*
* @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author lisps
*/

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

class action_plugin_linksuggest extends DokuWiki_Action_Plugin {
    
    /**
     * Register the eventhandlers
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, '_ajax_call');
    }
    
    /**
     * ajax Request Handler
     * 
     * 
     * 
     */
    function _ajax_call(&$event, $param) {
        if ($event->data !== 'plugin_linksuggest') {
            return;
        }
        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();

        global $INPUT;

        $page_ns  = trim($INPUT->post->str('ns')); //current namespace
        $page_id = trim($INPUT->post->str('id')); //current id
        $q = trim($INPUT->post->str('q')); //entered string

        //keep hashlink if exists
        $hash = null;
        if(strpos($q,'#') !== false) {
            list($q,$hash) = explode('#',$q,2);
        }
		$has_hash = $hash === null? false : true;
        $ns_user = $ns = getNS($q); //namespace of entered string
        $id = cleanID(noNS($q)); //page of entered string

        if($q && trim($q,'.') === '') { //only "." return
            $data = array();
        } else if($ns === ''){ // [[:xxx -> absolute link
            $data = $this->search_pages($ns,$id,$has_hash);
        } else if($ns === false && $page_ns) { // [[xxx and not in root-namespace
            $data = array_merge(
                    $this->search_pages($page_ns,$id,true),//search in current
                    $this->search_pages('',$id,$has_hash)            //and in root
            );
        } else if (strpos($ns,'.') !== false){ //relative link
            resolve_pageid($page_ns,$ns,$exists); //resolve the ns based on current id
            $data = $this->search_pages($ns,$id,$has_hash);
        } else {
            $data = $this->search_pages($ns,$id,$has_hash);
        }
        
        
        $data_r =array();
        $link= '';
        
        if($hash !== null && count($data) === 1 && $data[0]['type']==='f') {
            //if hash is given and a page was found
            $page = $data[0]['id'];
            $meta = p_get_metadata($page, false, METADATA_RENDER_USING_CACHE);

            if(isset($meta['internal']['toc'])) {
                $toc = $meta['description']['tableofcontents'];
                trigger_event('TPL_TOC_RENDER', $toc, null, false);
                if(is_array($toc) && count($toc) !== 0 ){
                    foreach($toc as &$t){ //loop through toc and compare
                        if($hash === '' || strpos($t['hid'],$hash) === 0){
                            $data_r[] = $t;
                        }
                    }
                    $link = $q; 
                }
            }
        } else {
            
            foreach($data as $entry){
                
                $data_r[] = array(
                    'id'=>noNS($entry['id']), 
                    'ns'=>($ns_user !== "")?$ns_user:':', //return what user has typed in
                    'type'=>$entry['type'], // d/f
                    'title'=>$entry['title'],
                    'rootns'=>$entry['ns']?0:1,
                );
            }
        }

        echo json_encode(array('data'=>$data_r,'deb'=>$deb,'link'=>$link));
    }
    

    
    protected  function search_pages($ns,$id,$pagesonly=false){
        global $conf;
        
        $data = array();
        $nsd  = utf8_encodeFN(str_replace(':','/',$ns)); //dir
        
        $opts = array(
                'depth' => 1,
                'listfiles' => true,
                'listdirs'  => !$pagesonly,
                'pagesonly' => true,
                'firsthead' => true,
                'sneakyacl' => $conf['sneaky_index'],
        );
        if($id) $opts['filematch'] = '^.*\/'.$id;
        if($id && !$pagesonly) $opts['dirmatch']  = '^.*\/'.$id;
        search($data,$conf['datadir'],'search_universal',$opts,$nsd);
        
        
        return $data;
    }
    
}