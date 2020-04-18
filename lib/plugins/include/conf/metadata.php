<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the Include Plugin
 *
 * @author    Esther Brunner <wikidesign@gmail.com>
 */
$meta['noheader']      = array('onoff');
$meta['firstseconly']  = array('onoff');
$meta['showtaglogos']  = array('onoff');
$meta['showlink']      = array('onoff');
$meta['showfooter']    = array('onoff');
$meta['showpermalink'] = array('onoff');
$meta['showdate']      = array('onoff');
$meta['showmdate']     = array('onoff');
$meta['showuser']      = array('onoff');
$meta['showcomments']  = array('onoff');
$meta['showlinkbacks'] = array('onoff');
$meta['showtags']      = array('onoff');
$meta['showeditbtn']   = array('onoff');
$meta['doredirect']    = array('onoff');
$meta['doindent']      = array('onoff');
$meta['linkonly']      = array('onoff');
$meta['title']         = array('onoff');
$meta['pageexists']    = array('onoff');
$meta['parlink']       = array('onoff');
$meta['safeindex']     = array('onoff');
$meta['order']         = array('multichoice', '_choices' => array('id', 'title', 'created', 'modified', 'indexmenu', 'custom'));
$meta['rsort']         = array('onoff');
$meta['depth']         = array('numeric', '_min' => 0);
$meta['readmore']      = array('onoff');
$meta['debugoutput']   = array('onoff');
//Setup VIM: ex: et ts=2 :
